<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kunjungans', function (Blueprint $table): void {
            // Nullable agar seluruh kunjungan lama tetap dapat dibaca sebelum
            // proses pengisian nomor antrean historis dilakukan pada tahap berikutnya.
            $table->char('kode_antrean', 1)->nullable()->after('poli_tujuan');
            $table->unsignedInteger('nomor_antrean')->nullable()->after('kode_antrean');
            $table->unique(
                ['tgl_kunjungan', 'poli_tujuan', 'nomor_antrean'],
                'unique_nomor_antrean_poli_harian'
            );
        });
    }

    public function down(): void
    {
        Schema::table('kunjungans', function (Blueprint $table): void {
            $table->dropUnique('unique_nomor_antrean_poli_harian');
            $table->dropColumn(['kode_antrean', 'nomor_antrean']);
        });
    }
};
