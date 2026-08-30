<?php

use App\Models\Pasien;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasiens', function (Blueprint $table) {
            $table->string('no_rm', 20)->nullable()->unique()->after('id_pasien');
        });

        // Beri nomor permanen juga untuk pasien yang sudah ada.
        DB::table('pasiens')->orderBy('id_pasien')->each(function (object $pasien): void {
            DB::table('pasiens')->where('id_pasien', $pasien->id_pasien)->update([
                'no_rm' => Pasien::nomorRekamMedisUntuk((int) $pasien->id_pasien),
            ]);
        });

        Schema::table('rekam_medises', function (Blueprint $table) {
            $table->foreignId('id_dokter')->nullable()->after('id_kunjungan')
                ->constrained('dokters', 'id_dokter')->nullOnDelete();
            $table->string('nama_dokter')->nullable()->after('id_dokter');
            $table->unique('id_kunjungan', 'rekam_medises_id_kunjungan_unique');
        });

        Schema::table('transaksis', function (Blueprint $table) {
            $table->unique('id_kunjungan', 'transaksis_id_kunjungan_unique');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropUnique('transaksis_id_kunjungan_unique');
        });

        Schema::table('rekam_medises', function (Blueprint $table) {
            $table->dropUnique('rekam_medises_id_kunjungan_unique');
            $table->dropConstrainedForeignId('id_dokter');
            $table->dropColumn('nama_dokter');
        });

        Schema::table('pasiens', function (Blueprint $table) {
            $table->dropUnique(['no_rm']);
            $table->dropColumn('no_rm');
        });
    }
};
