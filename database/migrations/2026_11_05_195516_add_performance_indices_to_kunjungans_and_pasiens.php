<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            // Index komposit untuk query antrean poli harian dokter agar instan
            $table->index(['poli_tujuan', 'tgl_kunjungan', 'status'], 'kunjungan_poli_tgl_status_index');
        });

        Schema::table('pasiens', function (Blueprint $table) {
            // Index untuk pencarian data pasien saat volume membesar
            $table->index('nama', 'pasien_nama_index');
            $table->index('nik', 'pasien_nik_index');
            $table->index('no_hp', 'pasien_no_hp_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropIndex('kunjungan_poli_tgl_status_index');
        });

        Schema::table('pasiens', function (Blueprint $table) {
            $table->dropIndex('pasien_nama_index');
            $table->dropIndex('pasien_nik_index');
            $table->dropIndex('pasien_no_hp_index');
        });
    }
};
