<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RekamMedisPasienController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $poliDokter = $user?->role === 'dokter' ? $user->dokter?->poli : null;

        abort_if($user?->role === 'dokter' && ! $poliDokter, 403, 'Profil dokter belum memiliki poli yang valid.');

        // Staff mencari seluruh data; dokter tetap dibatasi otomatis ke polinya sendiri.
        $poli = $poliDokter ?: 'semua';
        $query = Pasien::query()->whereHas('kunjungans.rekamMedis');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_rm', 'like', "%{$search}%");
            });
        }

        if ($poli !== 'semua') {
            $query->whereHas('kunjungans', fn ($q) => $q->where('poli_tujuan', $poli)->whereHas('rekamMedis'));
        }

        $rekamMedis = $query->with([
            'kunjungans' => function ($q) use ($poli) {
                $q->whereHas('rekamMedis')
                    ->when($poli !== 'semua', fn ($q) => $q->where('poli_tujuan', $poli))
                    ->with(['rekamMedis.dokter.user'])
                    ->latest('tgl_kunjungan')
                    ->latest('id_kunjungan');
            },
        ])->orderBy('nama')->paginate(15)->withQueryString();

        return view('rekam_medis_pasien.index', [
            'rekamMedis' => $rekamMedis,
            'isDokter' => $user?->role === 'dokter',
        ]);
    }

    public function show(Pasien $pasien)
    {
        $user = Auth::user();
        $poliDokter = $user?->role === 'dokter' ? $user->dokter?->poli : null;
        abort_if($user?->role === 'dokter' && ! $poliDokter, 403, 'Profil dokter belum memiliki poli yang valid.');

        $kunjunganQuery = $pasien->kunjungans()
            ->whereHas('rekamMedis')
            ->when($poliDokter, fn ($q) => $q->where('poli_tujuan', $poliDokter));

        abort_unless($kunjunganQuery->exists(), 404);

        $pasien->load([
            'kunjungans' => function ($q) use ($poliDokter) {
                $q->whereHas('rekamMedis')
                    ->when($poliDokter, fn ($q) => $q->where('poli_tujuan', $poliDokter))
                    ->with(['rekamMedis.dokter.user'])
                    ->latest('tgl_kunjungan')
                    ->latest('id_kunjungan');
            },
        ]);

        return view('rekam_medis_pasien.show', compact('pasien'));
    }
}
