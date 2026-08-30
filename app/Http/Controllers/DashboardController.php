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
            $totalPasien = Kunjungan::whereDate('tgl_kunjungan', now()->today())
                ->where('poli_tujuan', $poli)
                ->count();

            // Menunggu Diperiksa
            $antreanAktif = Kunjungan::whereDate('tgl_kunjungan', now()->today())
                ->where('poli_tujuan', $poli)
                ->whereIn('status', ['antre', 'menunggu_dokter'])
                ->count();

            // Selesai Diperiksa (siap_bayar atau selesai)
            $selesaiDiperiksa = Kunjungan::whereDate('tgl_kunjungan', now()->today())
                ->where('poli_tujuan', $poli)
                ->whereIn('status', ['siap_bayar', 'selesai'])
                ->count();

            // Daftar Antrean Pasien Poli Hari Ini
            $antreanHariIni = Kunjungan::with('pasien')
                ->whereDate('tgl_kunjungan', now()->today())
                ->where('poli_tujuan', $poli)
                ->oldest() // FIFO
                ->get();

            return view('dashboard_dokter', compact('totalPasien', 'antreanAktif', 'selesaiDiperiksa', 'antreanHariIni', 'dokter'));
        }

        $totalPasien = Pasien::count();
        $antreanAktif = Kunjungan::whereDate('tgl_kunjungan', now()->today())
            ->whereIn('status', ['antre', 'menunggu_dokter'])
            ->count();
        $menungguKasir = Kunjungan::whereDate('tgl_kunjungan', now()->today())
            ->where('status', 'siap_bayar')
            ->count();

        $antreanHariIni = Kunjungan::with('pasien')
            ->whereDate('tgl_kunjungan', now()->today())
            ->latest()
            ->get();

        return view('dashboard', compact('totalPasien', 'antreanAktif', 'menungguKasir', 'antreanHariIni'));
    }
}
