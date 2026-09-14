<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekam_medises', function (Blueprint $table): void {
            $table->text('hasil_pemeriksaan')->nullable()->after('keluhan');
            $table->text('catatan_tambahan')->nullable()->after('resep_obat');
        });
    }

    public function down(): void
    {
        Schema::table('rekam_medises', function (Blueprint $table): void {
            $table->dropColumn(['hasil_pemeriksaan', 'catatan_tambahan']);
        });
    }
};
