<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index()
    {
        $transaksis = Transaksi::with(['kunjungan.pasien', 'kunjungan.rekamMedis'])
            ->where('status_pembayaran', 'belum')
            ->oldest() // FIFO: yang lebih lama tampil di atas
            ->get();

        return view('transaksi.index', compact('transaksis'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'biaya_tindakan' => 'required|numeric|min:0',
            'biaya_obat'     => 'required|numeric|min:0',
            'uang_dibayar'   => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:cash,qr,debit',
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
                $biayaTindakan = round((float) $validated['biaya_tindakan'], 2);
                $biayaObat = round((float) $validated['biaya_obat'], 2);
                $totalBiaya = round($biayaTindakan + $biayaObat, 2);
                $uangDibayar = round((float) $validated['uang_dibayar'], 2);

                if ($uangDibayar < $totalBiaya) {
                    abort(422, 'Uang dibayar tidak boleh kurang dari total biaya.');
                }

                $transaksi->update([
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
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->back()->with('success', 'Pembayaran berhasil dicatat. Transaksi selesai!');
    }

    public function riwayat(Request $request)
    {
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
            $query->whereDate('updated_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('updated_at', '<=', $request->tanggal_sampai);
        }

        // Hitung ringkasan dari seluruh hasil filter sebelum query dibatasi pagination.
        $totalPendapatan = (clone $query)->sum('total_biaya');
        $jumlahTransaksi = (clone $query)->count();

        $riwayat = $query->latest('updated_at')->paginate(7);

        return view('transaksi.riwayat', compact('riwayat', 'totalPendapatan', 'jumlahTransaksi'));
    }
}
