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
        Schema::table('transaksis', function (Blueprint $table) {
            $table->decimal('biaya_tindakan', 12, 2)->default(0)->after('id_kunjungan');
            $table->decimal('biaya_obat', 12, 2)->default(0)->after('biaya_tindakan');
            $table->decimal('uang_dibayar', 12, 2)->default(0)->after('total_biaya');
            $table->decimal('kembalian', 12, 2)->default(0)->after('uang_dibayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['biaya_tindakan', 'biaya_obat', 'uang_dibayar', 'kembalian']);
        });
    }
};
