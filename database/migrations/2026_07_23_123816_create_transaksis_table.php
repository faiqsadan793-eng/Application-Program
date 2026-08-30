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
    Schema::create('transaksis', function (Blueprint $table) {
        $table->id('id_transaksi');
        // Foreign Key ke tabel kunjungans
        $table->foreignId('id_kunjungan')->constrained('kunjungans', 'id_kunjungan')->onDelete('cascade');
        $table->decimal('total_biaya', 12, 2)->default(0);
        $table->enum('status_pembayaran', ['lunas', 'belum'])->default('belum');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
