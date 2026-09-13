<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Dompdf\Dompdf;
use Dompdf\Options;
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
                    ->latest('id_kunjungan')->limit(1);
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

        $kunjungans = $kunjunganQuery->with('rekamMedis.dokter.user')
            ->latest('tgl_kunjungan')->latest('id_kunjungan')->paginate(10);

        return view('rekam_medis_pasien.show', compact('pasien', 'kunjungans'));
    }

    public function export(Request $request, Pasien $pasien)
    {
        $user = Auth::user();
        $poli = $user->role === 'dokter' ? $user->dokter?->poli : null;
        abort_if($user->role === 'dokter' && ! $poli, 403);
        $query = $pasien->kunjungans()->whereHas('rekamMedis')
            ->when($poli, fn ($q) => $q->where('poli_tujuan', $poli));
        abort_unless($query->exists(), 404);

        $total = $query->count();
        $parts = (int) ceil($total / 50);
        $validated = $request->validate(['bagian' => ['nullable', 'integer', 'min:1', 'max:'.$parts]]);
        // Riwayat besar tetap lengkap melalui beberapa PDF dengan pekerjaan terbatas per unduhan.
        if ($parts > 1 && empty($validated['bagian'])) {
            return redirect()->route('rekam-medis-pasien.show', $pasien)
                ->with('error', 'Riwayat panjang dibagi menjadi beberapa PDF, masing-masing maksimal 50 pemeriksaan. Pilih dan unduh setiap bagian untuk memperoleh seluruh riwayat.');
        }
        $part = (int) ($validated['bagian'] ?? 1);
        $kunjungans = $query->with('rekamMedis.dokter.user')
            ->latest('tgl_kunjungan')->latest('id_kunjungan')->skip(($part - 1) * 50)->take(50)->get();
        $options = new Options();
        $options->setIsRemoteEnabled(false);
        $options->setIsPhpEnabled(false);
        $options->setIsJavascriptEnabled(false);
        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('rekam_medis_pasien.pdf', compact('pasien', 'kunjungans', 'poli', 'part', 'parts', 'total'))->render());
        $pdf->setPaper('A4');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rekam-medis-'.$pasien->id_pasien.'-bagian-'.$part.'.pdf"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
