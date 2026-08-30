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
    Schema::create('kunjungans', function (Blueprint $table) {
        $table->id('id_kunjungan');
        // Foreign Key ke tabel pasiens
        $table->foreignId('id_pasien')->constrained('pasiens', 'id_pasien')->onDelete('cascade');
        $table->date('tgl_kunjungan');
        $table->enum('status', ['antre', 'menunggu_dokter', 'siap_bayar', 'selesai'])->default('antre');
        $table->string('poli_tujuan');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungans');
    }
};
