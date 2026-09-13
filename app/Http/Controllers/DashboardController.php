<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->role === 'dokter') {
            $dokter = $user->dokter;
            $poli = $dokter ? $dokter->poli : null;

            // Total Pasien Poli Hari Ini
            $totalPasien = Kunjungan::padaHariIni()
                ->where('poli_tujuan', $poli)
                ->count();

            // Menunggu Diperiksa
            $antreanAktif = Kunjungan::padaHariIni()
                ->where('poli_tujuan', $poli)
                ->whereIn('status', ['antre', 'menunggu_dokter'])
                ->count();

            // Selesai Diperiksa (siap_bayar atau selesai)
            $selesaiDiperiksa = Kunjungan::padaHariIni()
                ->where('poli_tujuan', $poli)
                ->whereIn('status', ['siap_bayar', 'selesai'])
                ->count();

            // Daftar Antrean Pasien Poli Hari Ini
            $antreanHariIni = Kunjungan::with('pasien')
                ->padaHariIni()
                ->where('poli_tujuan', $poli)
                ->orderBy('masuk_antrean_pada')
                ->orderBy('id_kunjungan')
                ->limit(100)
                ->get();

            return view('dashboard_dokter', compact('totalPasien', 'antreanAktif', 'selesaiDiperiksa', 'antreanHariIni', 'dokter'));
        }

        $totalPasien = Pasien::count();
        $antreanAktif = Kunjungan::padaHariIni()
            ->whereIn('status', ['antre', 'menunggu_dokter'])
            ->count();
        $menungguKasir = Kunjungan::padaHariIni()
            ->where('status', 'siap_bayar')
            ->count();

        $antreanHariIni = Kunjungan::with('pasien')
            ->padaHariIni()
            ->orderBy('masuk_antrean_pada')
            ->orderBy('id_kunjungan')
            ->limit(100)
            ->get();

        return view('dashboard', compact('totalPasien', 'antreanAktif', 'menungguKasir', 'antreanHariIni'));
    }
}
