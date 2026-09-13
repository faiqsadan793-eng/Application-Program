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
            $table->timestamp('masuk_antrean_pada', precision: 6)->nullable()->after('active_tgl_kunjungan');
            $table->string('kategori_pembatalan', 50)->nullable()->after('poli_tujuan');
        });

        Schema::table('transaksis', function (Blueprint $table) {
            $table->index(['status_pembayaran', 'created_at'], 'transaksi_status_dibuat_index');
            $table->index(['status_pembayaran', 'updated_at'], 'transaksi_status_diperbarui_index');
        });

        // Satu query terukur; aman untuk data lama dan tidak memuat baris ke memori PHP.
        DB::table('kunjungans')
            ->whereNull('masuk_antrean_pada')
            ->update(['masuk_antrean_pada' => DB::raw('created_at')]);

        Schema::table('kunjungans', function (Blueprint $table) {
            // Mengikuti filter antrean harian sehingga ratusan/ribuan histori tidak dipindai penuh.
            $table->index(
                ['poli_tujuan', 'tgl_kunjungan', 'status', 'masuk_antrean_pada'],
                'kunjungan_antrean_poli_harian_index'
            );
            $table->index(['status', 'tgl_kunjungan'], 'kunjungan_status_tanggal_index');
            $table->index('tgl_kunjungan', 'kunjungan_tanggal_index');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropIndex('transaksi_status_dibuat_index');
            $table->dropIndex('transaksi_status_diperbarui_index');
        });

        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropIndex('kunjungan_antrean_poli_harian_index');
            $table->dropIndex('kunjungan_status_tanggal_index');
            $table->dropIndex('kunjungan_tanggal_index');
            $table->dropColumn(['masuk_antrean_pada', 'kategori_pembatalan']);
        });
    }
};
