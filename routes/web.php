<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\KunjunganController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RekamMedisController;
use App\Http\Controllers\RekamMedisPasienController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// ROUTE KHUSUS STAFF
Route::middleware(['auth', 'role:staff'])->group(function () {
    // Master data dokter dan akun login dokter
    Route::resource('dokter', DokterController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // Pasien: full CRUD
    Route::resource('pasien', PasienController::class)
        ->only(['index', 'create', 'store', 'update', 'destroy']);

    // Kunjungan: lihat riwayat, detail, dan buat antrian baru
    Route::resource('kunjungan', KunjunganController::class)
        ->only(['index', 'show', 'store']);

    // Transaksi: hanya lihat tagihan dan proses pembayaran
    Route::resource('transaksi', TransaksiController::class)
        ->only(['index', 'update']);

    // Riwayat Transaksi: histori transaksi yang sudah lunas & ekspor laporan
    Route::get('riwayat-transaksi', [TransaksiController::class, 'riwayat'])
        ->name('riwayat-transaksi.index');
    Route::get('riwayat-transaksi/export', [TransaksiController::class, 'exportCsv'])
        ->name('riwayat-transaksi.export');
});

// Pembatalan & pengembalian antrean dapat dilakukan oleh staff dan dokter
Route::middleware(['auth', 'role:staff,dokter'])->group(function () {
    Route::patch('kunjungan/{kunjungan}/batalkan', [KunjunganController::class, 'batalkan'])
        ->name('kunjungan.batalkan');
    Route::patch('kunjungan/{kunjungan}/kembalikan-antrean', [KunjunganController::class, 'kembalikanKeAntrean'])
        ->name('kunjungan.kembalikan-antrean');
});

// ROUTE KHUSUS DOKTER
Route::middleware(['auth', 'role:dokter'])->group(function () {
    Route::get('dashboard/antrean-check', [DashboardController::class, 'checkAntreanDokter'])
        ->name('dashboard.dokter.antrean-check');

    Route::post('rekam-medis/panggil-selanjutnya', [RekamMedisController::class, 'panggilSelanjutnya'])
        ->name('rekam-medis.panggil-selanjutnya');
    Route::post('rekam-medis/{id}/mulai', [RekamMedisController::class, 'mulaiPeriksa'])
        ->name('rekam-medis.mulai');

    // Rekam Medis: hanya lihat antrian dan simpan hasil periksa
    Route::resource('rekam-medis', RekamMedisController::class)
        ->only(['index', 'store']);

    // Riwayat Rekam Medis Pasien: hanya dapat diakses oleh dokter
    Route::get('rekam-medis-pasien', [RekamMedisPasienController::class, 'index'])
        ->name('rekam-medis-pasien.index');
    Route::get('rekam-medis-pasien/{pasien}', [RekamMedisPasienController::class, 'show'])
        ->name('rekam-medis-pasien.show');
    Route::get('rekam-medis-pasien/{pasien}/pdf', [RekamMedisPasienController::class, 'export'])
        ->middleware('throttle:6,1')->name('rekam-medis-pasien.pdf');
});

// Profile: semua user yang sudah login
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
