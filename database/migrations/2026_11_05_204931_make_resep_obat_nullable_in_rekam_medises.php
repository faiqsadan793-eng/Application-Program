<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadikan kolom resep_obat nullable agar poli tanpa resep obat
     * (misal: Poli Gigi, Poli Spesialis Kandungan) tetap dapat menyimpan rekam medis.
     */
    public function up(): void
    {
        Schema::table('rekam_medises', function (Blueprint $table) {
            $table->text('resep_obat')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('rekam_medises', function (Blueprint $table) {
            // Kembalikan ke NOT NULL; pastikan tidak ada data null sebelum rollback.
            $table->text('resep_obat')->nullable(false)->change();
        });
    }
};
