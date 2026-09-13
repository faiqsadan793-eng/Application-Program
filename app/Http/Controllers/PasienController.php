<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Daftar poli terpusat agar konsisten di seluruh aplikasi
// Format: 'value_form' => 'nama_poli_di_DB'
const POLI_LIST = [
    'Poli Umum'  => 'Poli Umum',
    'Poli Gigi'  => 'Poli Gigi',
    'Poli Anak'  => 'Poli Anak',
];

class PasienController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $hasSearch = $search !== '';

        // Satu tabel untuk pasien terbaru dan hasil pencarian. Tidak memuat
        // seluruh data sekaligus agar halaman tetap ringan saat data bertambah.
        $pasiens = Pasien::query()
            ->withExists(['kunjungans as has_active_kunjungan_today' => function ($query) {
                $query->padaHariIni()
                    ->whereIn('status', Kunjungan::STATUS_AKTIF);
            }])
            ->when($hasSearch, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('no_rm', 'like', "%{$search}%");

                // Cari by id_pasien hanya jika input berupa angka murni
                if (is_numeric($search)) {
                    $q->orWhere('id_pasien', (int) $search);
                }
                });
            })
            ->latest()
            ->paginate(7)
            ->withQueryString();

        $antreanHariIni = Kunjungan::with('pasien')
            ->padaHariIni()
            ->orderBy('masuk_antrean_pada')
            ->orderBy('id_kunjungan')
            ->get();

        return view('pasien.index', compact('pasiens', 'antreanHariIni', 'hasSearch'));
    }

    public function create()
    {
        return view('input.pasien', ['poliList' => POLI_LIST]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'          => 'required|string|max:255',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'no_hp'         => 'required|string|max:15',
            'nik'           => 'nullable|string|size:16',
            'pekerjaan'     => 'nullable|string|max:255',
            'alamat'        => 'nullable|string|max:500',
            'poli_tujuan'   => 'required|string|in:' . implode(',', array_keys(POLI_LIST)),
        ]);

        $pasien = DB::transaction(function () use ($validated): Pasien {
            $pasien = Pasien::create([
                'nama'          => $validated['nama'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'no_hp'         => $validated['no_hp'],
                'nik'           => $validated['nik'] ?? null,
                'pekerjaan'     => $validated['pekerjaan'] ?? null,
                'alamat'        => $validated['alamat'] ?? null,
            ]);

            $pasien->no_rm = Pasien::nomorRekamMedisUntuk($pasien->id_pasien);
            $pasien->save();

            Kunjungan::create([
                'id_pasien'     => $pasien->id_pasien,
                'tgl_kunjungan' => Kunjungan::tanggalHariIni(),
                'poli_tujuan'   => $validated['poli_tujuan'],
                'status'        => Kunjungan::STATUS_ANTRE,
            ]);

            return $pasien;
        });

        return redirect()->route('pasien.index')
            ->with('success', 'Pasien ' . $pasien->nama . ' berhasil didaftarkan dan masuk antrean.');
    }

    public function update(Request $request, int $id)
    {
        $pasien = Pasien::findOrFail($id);

        $validated = $request->validate([
            'nama'          => 'required|string|max:255',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'no_hp'         => 'required|string|max:15',
            'nik'           => 'nullable|string|size:16',
            'pekerjaan'     => 'nullable|string|max:255',
            'alamat'        => 'nullable|string|max:500',
        ]);

        $pasien->update($validated);

        return redirect()->route('pasien.index')
            ->with('success', 'Data pasien ' . $pasien->nama . ' berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $pasien = Pasien::findOrFail($id);

        // Riwayat medis tidak boleh ikut hilang lewat penghapusan data pasien.
        if ($pasien->kunjungans()->exists()) {
            return redirect()->back()
                ->with('error', 'Pasien ' . $pasien->nama . ' tidak bisa dihapus karena sudah memiliki riwayat kunjungan.');
        }

        $pasien->delete();

        return redirect()->route('pasien.index')
            ->with('success', 'Data pasien ' . $pasien->nama . ' berhasil dihapus.');
    }
}
