<?php

namespace App\Http\Controllers;

use App\Models\Kunjungan;
use Illuminate\Http\Request;

class KunjunganController extends Controller
{
    public function index(Request $request)
    {
        $query = Kunjungan::with(['pasien', 'rekamMedis', 'transaksi']);

        // --- Filter: Cari nama pasien atau No. RM ---
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                // Cari berdasarkan nama pasien
                $q->whereHas('pasien', function ($qp) use ($search) {
                    $qp->where('nama', 'like', "%{$search}%")
                       ->orWhere('nik', 'like', "%{$search}%")
                       ->orWhere('no_rm', 'like', "%{$search}%");
                });
                // Tetap dukung pencarian nomor kunjungan internal jika input angka.
                if (is_numeric($search)) {
                    $q->orWhere('id_kunjungan', (int) $search);
                }
            });
        }

        // --- Filter: Rentang Waktu ---
        $rentang = $request->input('rentang', '7');
        if ($rentang === '7') {
            $query->where('tgl_kunjungan', '>=', now()->subDays(7)->toDateString());
        } elseif ($rentang === '30') {
            $query->where('tgl_kunjungan', '>=', now()->subDays(30)->toDateString());
        } elseif ($rentang === 'bulan') {
            $query->whereMonth('tgl_kunjungan', now()->month)
                  ->whereYear('tgl_kunjungan', now()->year);
        }
        // 'semua' = tidak ada filter waktu

        // --- Filter: Poli ---
        if ($request->filled('poli') && $request->input('poli') !== 'semua') {
            $query->where('poli_tujuan', $request->input('poli'));
        }

        // --- Filter: Status ---
        if ($request->filled('status') && $request->input('status') !== 'semua') {
            $statusMap = [
                'menunggu' => ['antre', 'menunggu_dokter'],
                'siap_bayar' => ['siap_bayar'],
                'selesai'  => ['selesai'],
            ];
            $statusFilter = $statusMap[$request->input('status')] ?? null;
            if ($statusFilter) {
                $query->whereIn('status', $statusFilter);
            }
        }

        $kunjungans = $query->latest('tgl_kunjungan')
                            ->latest('id_kunjungan')
                            ->paginate(7)
                            ->withQueryString(); // pertahankan filter di pagination

        return view('kunjungan.index', compact('kunjungans'));
    }

    public function show($id)
    {
        $kunjungan = Kunjungan::with([
            'pasien',
            'rekamMedis',
            'transaksi',
        ])->findOrFail($id);

        return view('kunjungan.show', compact('kunjungan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pasien'   => 'required|exists:pasiens,id_pasien',
            'poli_tujuan' => 'required|string|in:Poli Umum,Poli Gigi,Poli Anak',
        ]);

        // Cek di level aplikasi — cegah sebelum sampai ke DB
        $sudahAntri = Kunjungan::where('id_pasien', $validated['id_pasien'])
            ->whereDate('tgl_kunjungan', now()->toDateString())
            ->whereIn('status', Kunjungan::STATUS_AKTIF)
            ->exists();

        if ($sudahAntri) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Pasien ini masih memiliki kunjungan aktif hari ini. Selesaikan pembayaran terlebih dahulu sebelum membuat kunjungan baru.');
        }

        try {
            Kunjungan::create([
                'id_pasien'     => $validated['id_pasien'],
                'poli_tujuan'   => $validated['poli_tujuan'],
                'tgl_kunjungan' => now()->toDateString(),
                'status'        => 'antre',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Pasien ini masih memiliki kunjungan aktif hari ini. Selesaikan pembayaran terlebih dahulu sebelum membuat kunjungan baru.');
            }
            throw $e;
        }

        $redirectUrl = route('pasien.index');
        if ($request->filled('search_query')) {
            $redirectUrl = route('pasien.index', ['search' => $request->input('search_query')]);
        }

        return redirect($redirectUrl)->with('success', 'Pasien berhasil masuk antrean.');
    }
}
