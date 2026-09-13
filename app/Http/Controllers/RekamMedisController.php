<?php

namespace App\Http\Controllers;

use App\Models\Kunjungan;
use App\Models\RekamMedis;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RekamMedisController extends Controller
{
    public function index()
    {
        $dokter = $this->dokterAktif();

        if (! $dokter) {
            abort(403, 'Akun dokter belum memiliki profil dan poli yang valid.');
        }

        // Antrean baru hanya berasal dari hari ini. Pemeriksaan yang sudah dimulai
        // tetap ditampilkan sampai selesai meskipun tanggal telah berganti.
        $antrean = Kunjungan::with('pasien')
            ->where('poli_tujuan', $dokter->poli)
            ->where(function ($query) {
                $query->where('status', Kunjungan::STATUS_MENUNGGU_DOKTER)
                    ->orWhere(function ($today) {
                        $today->padaHariIni()
                            ->where('status', Kunjungan::STATUS_ANTRE);
                    });
            })
            ->orderBy('masuk_antrean_pada')
            ->orderBy('id_kunjungan')
            ->get();

        return view('rekam_medis.index', compact('antrean'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kunjungan' => 'required|exists:kunjungans,id_kunjungan',
            'keluhan'      => 'required|string|max:2000',
            'diagnosa'     => 'required|string|max:2000',
            'resep_obat'   => 'required|string|max:2000',
        ]);

        $dokter = $this->dokterAktif();
        if (! $dokter) {
            return redirect()->back()->with('error', 'Profil dokter belum tersedia. Hubungi staff klinik.');
        }

        try {
            $kunjungan = DB::transaction(function () use ($validated, $dokter): Kunjungan {
                $kunjungan = $this->kunjunganUntukDokter($validated['id_kunjungan'], $dokter->poli, true);

                if ($kunjungan->status !== Kunjungan::STATUS_MENUNGGU_DOKTER) {
                    abort(422, 'Kunjungan belum dimulai atau sudah selesai diproses.');
                }

                if (RekamMedis::where('id_kunjungan', $kunjungan->id_kunjungan)->exists()) {
                    abort(422, 'Rekam medis untuk kunjungan ini sudah pernah disimpan.');
                }

                RekamMedis::create([
                    'id_kunjungan' => $kunjungan->id_kunjungan,
                    'id_dokter'    => $dokter->id_dokter,
                    // Snapshot menjaga identitas pemeriksa pada histori bila akun dokter berubah/dihapus.
                    'nama_dokter'  => Auth::user()->name,
                    'keluhan'      => $validated['keluhan'],
                    'diagnosa'     => $validated['diagnosa'],
                    'resep_obat'   => $validated['resep_obat'],
                ]);

                $kunjungan->update(['status' => Kunjungan::STATUS_SIAP_BAYAR]);

                Transaksi::firstOrCreate(
                    ['id_kunjungan' => $kunjungan->id_kunjungan],
                    ['total_biaya' => 0, 'status_pembayaran' => Transaksi::STATUS_BELUM],
                );

                return $kunjungan;
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            if ($exception->getStatusCode() === 404) {
                throw $exception;
            }

            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->back()
            ->with('success', 'Pemeriksaan selesai. Pasien ' . ($kunjungan->pasien->nama ?? '') . ' diarahkan ke kasir.')
            ->with('completed_visit_id', $kunjungan->id_kunjungan);
    }

    public function mulaiPeriksa(int $id)
    {
        $dokter = $this->dokterAktif();
        if (! $dokter) {
            return redirect()->back()->with('error', 'Profil dokter belum tersedia. Hubungi staff klinik.');
        }

        try {
            $kunjungan = DB::transaction(function () use ($id, $dokter): Kunjungan {
                // Sertakan pemeriksaan lintas hari agar tetap hanya ada satu pasien
                // yang sedang diperiksa pada poli ini.
                $antreanPoli = Kunjungan::where('poli_tujuan', $dokter->poli)
                    ->whereIn('status', [Kunjungan::STATUS_ANTRE, Kunjungan::STATUS_MENUNGGU_DOKTER])
                    ->where(function ($query) {
                        $query->padaHariIni()
                            ->orWhere('status', Kunjungan::STATUS_MENUNGGU_DOKTER);
                    })
                    ->lockForUpdate()
                    ->get();

                $kunjungan = $antreanPoli->firstWhere('id_kunjungan', $id);
                abort_unless($kunjungan, 404);

                $kunjunganAktif = $antreanPoli->firstWhere('status', Kunjungan::STATUS_MENUNGGU_DOKTER);

                if ($kunjungan->status === Kunjungan::STATUS_MENUNGGU_DOKTER) {
                    return $kunjungan;
                }

                if ($kunjungan->status !== Kunjungan::STATUS_ANTRE) {
                    abort(422, 'Kunjungan ini sudah tidak dapat dimulai karena statusnya bukan antrean.');
                }

                if ($kunjunganAktif) {
                    abort(422, 'Masih ada pasien yang sedang diperiksa di poli ini. Selesaikan pemeriksaan tersebut terlebih dahulu.');
                }

                $kunjungan->update(['status' => Kunjungan::STATUS_MENUNGGU_DOKTER]);

                return $kunjungan;
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            if ($exception->getStatusCode() === 404) {
                throw $exception;
            }

            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->route('rekam-medis.index')
            ->with('success', 'Mulai memeriksa pasien ' . ($kunjungan->pasien->nama ?? '') . '.');
    }

    public function panggilSelanjutnya()
    {
        /** @var \App\Models\User|null $user */
        $dokter = $this->dokterAktif();
        if (! $dokter) {
            return redirect()->back()->with('error', 'Profil dokter belum tersedia. Hubungi staff klinik.');
        }

        $kunjungan = DB::transaction(function () use ($dokter): ?Kunjungan {
            // Pemeriksaan lintas hari ikut dikunci agar antrean baru tidak dipanggil
            // sebelum pemeriksaan sebelumnya selesai.
            $antreanPoli = Kunjungan::where('poli_tujuan', $dokter->poli)
                ->whereIn('status', [Kunjungan::STATUS_ANTRE, Kunjungan::STATUS_MENUNGGU_DOKTER])
                ->where(function ($query) {
                    $query->padaHariIni()
                        ->orWhere('status', Kunjungan::STATUS_MENUNGGU_DOKTER);
                })
                ->lockForUpdate()
                ->get();

            $sedangDiperiksa = $antreanPoli->firstWhere('status', Kunjungan::STATUS_MENUNGGU_DOKTER);

            if ($sedangDiperiksa) {
                return null;
            }

            $kunjungan = $antreanPoli
                ->where('status', Kunjungan::STATUS_ANTRE)
                ->sortBy(fn (Kunjungan $item) => sprintf(
                    '%s-%020d',
                    $item->masuk_antrean_pada?->format('YmdHis.u') ?? $item->created_at->format('YmdHis.u'),
                    $item->id_kunjungan,
                ))
                ->first();

            if ($kunjungan) {
                $kunjungan->update(['status' => Kunjungan::STATUS_MENUNGGU_DOKTER]);
            }

            return $kunjungan;
        });

        if ($kunjungan) {
            return redirect()->route('rekam-medis.index')
                ->with('success', 'Memanggil pasien selanjutnya: ' . ($kunjungan->pasien->nama ?? '') . '.');
        }

        return redirect()->route('rekam-medis.index')
            ->with('info', 'Tidak ada antrean yang dapat dipanggil. Selesaikan pasien yang sedang diperiksa terlebih dahulu.');
    }

    private function dokterAktif(): ?\App\Models\Dokter
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->dokter;
    }

    private function kunjunganUntukDokter(int $idKunjungan, string $poli, bool $lock = false): Kunjungan
    {
        $query = Kunjungan::whereKey($idKunjungan)
            ->where('poli_tujuan', $poli)
            ->where(function ($query) {
                $query->padaHariIni()
                    ->orWhere('status', Kunjungan::STATUS_MENUNGGU_DOKTER);
            });

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->firstOrFail();
    }
}
