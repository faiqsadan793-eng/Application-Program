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
    Schema::create('rekam_medises', function (Blueprint $table) {
        $table->id('id_rm');
        // Foreign Key ke tabel kunjungans
        $table->foreignId('id_kunjungan')->constrained('kunjungans', 'id_kunjungan')->onDelete('cascade');
        $table->text('keluhan');
        $table->text('diagnosa');
        $table->text('resep_obat');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekam_medises');
    }
};
