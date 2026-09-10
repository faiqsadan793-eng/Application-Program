<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->enum('status', ['antre', 'menunggu_dokter', 'siap_bayar', 'selesai', 'dibatalkan'])
                ->default('antre')
                ->change();
            $table->string('alasan_pembatalan', 500)->nullable()->after('poli_tujuan');
            $table->timestamp('dibatalkan_pada')->nullable()->after('alasan_pembatalan');
            $table->foreignId('dibatalkan_oleh')->nullable()->after('dibatalkan_pada')
                ->constrained('users')->nullOnDelete();
            $table->string('nama_pembatal')->nullable()->after('dibatalkan_oleh');
        });
    }

    public function down(): void
    {
        // Gunakan status terminal agar rollback tidak menciptakan dua antrean aktif
        // untuk pasien yang telah mendaftar ulang setelah pembatalan.
        DB::table('kunjungans')->where('status', 'dibatalkan')->update([
            'status' => 'selesai',
            'active_tgl_kunjungan' => null,
        ]);

        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dibatalkan_oleh');
            $table->dropColumn(['alasan_pembatalan', 'dibatalkan_pada', 'nama_pembatal']);
            $table->enum('status', ['antre', 'menunggu_dokter', 'siap_bayar', 'selesai'])
                ->default('antre')
                ->change();
        });
    }
};
