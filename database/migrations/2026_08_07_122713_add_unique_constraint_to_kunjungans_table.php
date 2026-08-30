<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bersihkan data duplikat antrian lalu pasang unique constraint
     * agar satu pasien hanya bisa punya satu antrian aktif per hari.
     *
     * "Aktif" = status IN ('antre', 'menunggu_dokter', 'siap_bayar').
     * Unique constraint di DB hanya bisa dibuat pada kolom, bukan pada kondisi WHERE,
     * jadi kita tangani uniqueness di level aplikasi (controller) + bersihkan data lama.
     */
    public function up(): void
    {
        // --- STEP 1: Hapus data duplikat yang sudah ada ---
        // Strategi: untuk setiap (id_pasien, tgl_kunjungan) yang duplikat,
        // pertahankan record dengan id_kunjungan TERKECIL (yang pertama masuk),
        // hapus sisanya.
        $duplicates = DB::select("
            SELECT id_pasien, tgl_kunjungan
            FROM kunjungans
            GROUP BY id_pasien, tgl_kunjungan
            HAVING COUNT(*) > 1
        ");

        foreach ($duplicates as $dup) {
            // Ambil semua id untuk kombinasi duplikat ini, urutkan dari terkecil
            $ids = DB::table('kunjungans')
                ->where('id_pasien', $dup->id_pasien)
                ->whereDate('tgl_kunjungan', $dup->tgl_kunjungan)
                ->orderBy('id_kunjungan', 'asc')
                ->pluck('id_kunjungan')
                ->toArray();

            // Pertahankan yang pertama (index 0), hapus sisanya
            $toDelete = array_slice($ids, 1);

            if (!empty($toDelete)) {
                // Hapus transaksi & rekam medis yang terkait dulu (jaga FK integrity)
                DB::table('transaksis')->whereIn('id_kunjungan', $toDelete)->delete();
                DB::table('rekam_medises')->whereIn('id_kunjungan', $toDelete)->delete();
                DB::table('kunjungans')->whereIn('id_kunjungan', $toDelete)->delete();
            }
        }

        // --- STEP 2: Tambahkan unique index di level DB ---
        // Ini memastikan tidak ada duplikat (id_pasien + tgl_kunjungan) di DB sama sekali
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->unique(['id_pasien', 'tgl_kunjungan'], 'unique_pasien_per_hari');
        });
    }

    public function down(): void
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropUnique('unique_pasien_per_hari');
        });
    }
};
