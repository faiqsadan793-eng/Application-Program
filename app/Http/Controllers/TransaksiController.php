<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TransaksiController extends Controller
{
    private const MAX_NOMINAL = 9_999_999_999;

    public function index()
    {
        $transaksis = Transaksi::with(['kunjungan.pasien', 'kunjungan.rekamMedis'])
            ->where('status_pembayaran', 'belum')
            ->oldest() // FIFO: yang lebih lama tampil di atas
            ->paginate(25);

        return view('transaksi.index', compact('transaksis'));
    }

    public function update(Request $request, $id)
    {
        // Dipakai untuk membuka kembali transaksi yang sama jika validasi gagal.
        $request->merge(['payment_transaction_id' => (int) $id]);

        $validated = $request->validate([
            'payment_transaction_id' => ['required', 'integer'],
            'biaya_jasa_dokter' => ['required', 'integer', 'min:0', 'max:'.self::MAX_NOMINAL],
            'biaya_tindakan' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_NOMINAL],
            'biaya_obat' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_NOMINAL],
            'uang_dibayar' => ['required', 'integer', 'min:0', 'max:'.self::MAX_NOMINAL],
            'metode_pembayaran' => ['required', 'in:cash,qr,debit'],
        ]);

        try {
            DB::transaction(function () use ($id, $validated): void {
                // Lock mencegah dua kasir melunasi transaksi yang sama pada waktu bersamaan.
                $transaksi = Transaksi::query()
                    ->with('kunjungan')
                    ->lockForUpdate()
                    ->findOrFail($id);

                if ($transaksi->status_pembayaran === Transaksi::STATUS_LUNAS) {
                    abort(422, 'Transaksi ini sudah lunas dan tidak bisa diubah.');
                }

                if (! $transaksi->kunjungan) {
                    abort(422, 'Data kunjungan tidak ditemukan. Hubungi administrator.');
                }

                if ($transaksi->kunjungan->status !== \App\Models\Kunjungan::STATUS_SIAP_BAYAR) {
                    abort(422, 'Transaksi hanya dapat diproses setelah pemeriksaan selesai.');
                }

                // Nilai total dan kembalian harus selalu dihitung server, bukan dari input browser.
                $biayaJasaDokter = round((float) $validated['biaya_jasa_dokter'], 2);
                $biayaTindakan = round((float) ($validated['biaya_tindakan'] ?? 0), 2);
                $biayaObat = round((float) ($validated['biaya_obat'] ?? 0), 2);
                $totalBiaya = round($biayaJasaDokter + $biayaTindakan + $biayaObat, 2);
                $uangDibayar = round((float) $validated['uang_dibayar'], 2);

                if ($totalBiaya > self::MAX_NOMINAL) {
                    abort(422, 'Total biaya melebihi batas nominal yang dapat disimpan.');
                }

                if ($uangDibayar < $totalBiaya) {
                    abort(422, 'Uang dibayar tidak boleh kurang dari total biaya.');
                }

                $transaksi->update([
                    'biaya_jasa_dokter' => $biayaJasaDokter,
                    'biaya_tindakan'    => $biayaTindakan,
                    'biaya_obat'        => $biayaObat,
                    'total_biaya'       => $totalBiaya,
                    'uang_dibayar'      => $uangDibayar,
                    'kembalian'         => round($uangDibayar - $totalBiaya, 2),
                    'metode_pembayaran' => $validated['metode_pembayaran'],
                    'status_pembayaran' => Transaksi::STATUS_LUNAS,
                ]);

                $transaksi->kunjungan->update(['status' => \App\Models\Kunjungan::STATUS_SELESAI]);
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->back()
            ->with('success', 'Pembayaran berhasil dicatat. Transaksi selesai!')
            ->with('completed_payment_id', (int) $id);
    }

    public function riwayat(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'tanggal_dari' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:tanggal_dari'],
        ]);

        $query = Transaksi::with(['kunjungan.pasien', 'kunjungan.rekamMedis'])
            ->where('status_pembayaran', 'lunas');

        // Filter pencarian nama pasien atau no transaksi
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('kunjungan.pasien', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query->where('updated_at', '>=', Carbon::parse($request->tanggal_dari, 'Asia/Jakarta')->startOfDay());
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('updated_at', '<', Carbon::parse($request->tanggal_sampai, 'Asia/Jakarta')->addDay()->startOfDay());
        }

        // Hitung ringkasan dari seluruh hasil filter sebelum query dibatasi pagination.
        $totalPendapatan = (clone $query)->sum('total_biaya');
        $jumlahTransaksi = (clone $query)->count();

        $riwayat = $query->latest('updated_at')->paginate(7);

        return view('transaksi.riwayat', compact('riwayat', 'totalPendapatan', 'jumlahTransaksi'));
    }

    /**
     * Ekspor Laporan Keuangan & Kasir Transaksi ke format CSV / Excel
     */
    public function exportCsv(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'tanggal_dari' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:tanggal_dari'],
        ]);

        $query = Transaksi::with(['kunjungan.pasien', 'kunjungan.rekamMedis'])
            ->where('status_pembayaran', 'lunas');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('kunjungan.pasien', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('tanggal_dari')) {
            $query->where('updated_at', '>=', Carbon::parse($request->tanggal_dari, 'Asia/Jakarta')->startOfDay());
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('updated_at', '<', Carbon::parse($request->tanggal_sampai, 'Asia/Jakarta')->addDay()->startOfDay());
        }

        $transaksis = $query->latest('updated_at')->get();

        $filename = 'Laporan_Keuangan_Klinik_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($transaksis) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM agar terbaca rapi saat dibuka di Microsoft Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Header Kolom Laporan
            fputcsv($handle, [
                'No. Transaksi',
                'Tanggal & Waktu Pembayaran',
                'No. RM',
                'Nama Pasien',
                'Poli Tujuan',
                'Diagnosa Dokter',
                'Jasa Dokter (Rp)',
                'Biaya Tindakan (Rp)',
                'Biaya Obat (Rp)',
                'Total Biaya (Rp)',
                'Metode Pembayaran',
                'Uang Diterima (Rp)',
                'Kembalian (Rp)',
                'Status',
            ], ';');

            $totalSemua = 0;

            foreach ($transaksis as $t) {
                $totalSemua += (int) $t->total_biaya;
                $noRm = $t->kunjungan->pasien->no_rm ?? ('RM-' . str_pad($t->kunjungan->id_pasien, 4, '0', STR_PAD_LEFT));
                $waktu = $t->updated_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');

                fputcsv($handle, [
                    'TRX-' . str_pad($t->id_transaksi, 5, '0', STR_PAD_LEFT),
                    $waktu,
                    $noRm,
                    $t->kunjungan->pasien->nama ?? '-',
                    $t->kunjungan->poli_tujuan ?? '-',
                    $t->kunjungan->rekamMedis->diagnosa ?? '-',
                    (int) ($t->biaya_jasa_dokter ?? 50000),
                    (int) $t->biaya_tindakan,
                    (int) $t->biaya_obat,
                    (int) $t->total_biaya,
                    Transaksi::labelMetodePembayaran($t->metode_pembayaran),
                    (int) $t->uang_dibayar,
                    (int) $t->kembalian,
                    'Lunas',
                ], ';');
            }

            // Baris Total di paling bawah
            fputcsv($handle, [
                'TOTAL OMSET PENDAPATAN',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $totalSemua,
                '',
                '',
                '',
                '',
            ], ';');

            fclose($handle);
        }, 200, $headers);
    }
}
