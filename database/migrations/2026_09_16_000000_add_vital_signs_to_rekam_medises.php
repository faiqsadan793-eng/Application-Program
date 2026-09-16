<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekam_medises', function (Blueprint $table): void {
            $table->decimal('suhu_tubuh', 4, 1)->nullable()->after('keluhan');
            $table->decimal('tinggi_badan', 5, 2)->nullable()->after('suhu_tubuh');
            $table->decimal('berat_badan', 5, 2)->nullable()->after('tinggi_badan');
            $table->string('tekanan_darah', 7)->nullable()->after('berat_badan');
        });
    }

    public function down(): void
    {
        Schema::table('rekam_medises', function (Blueprint $table): void {
            $table->dropColumn(['suhu_tubuh', 'tinggi_badan', 'berat_badan', 'tekanan_darah']);
        });
    }
};
