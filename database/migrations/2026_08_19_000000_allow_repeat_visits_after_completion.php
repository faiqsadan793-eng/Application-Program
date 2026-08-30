<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pasien boleh membuat kunjungan ulang pada hari yang sama setelah kunjungan
     * sebelumnya selesai. Kolom nullable ini hanya berisi tanggal saat kunjungan
     * masih aktif; unique index tetap melindungi dari dua kunjungan aktif bersamaan.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('kunjungans', 'active_tgl_kunjungan')) {
            Schema::table('kunjungans', function (Blueprint $table) {
                $table->date('active_tgl_kunjungan')->nullable()->after('tgl_kunjungan');
            });
        }

        DB::table('kunjungans')
            ->whereIn('status', ['antre', 'menunggu_dokter', 'siap_bayar'])
            ->update(['active_tgl_kunjungan' => DB::raw('tgl_kunjungan')]);

        // Indeks lama menjadi pendukung foreign key id_pasien. Buat indeks pengganti
        // lebih dulu agar MySQL mengizinkan unique index lama dilepas.
        if (! $this->hasIndex('kunjungans', 'kunjungans_id_pasien_index')) {
            Schema::table('kunjungans', function (Blueprint $table) {
                $table->index('id_pasien', 'kunjungans_id_pasien_index');
            });
        }

        if ($this->hasIndex('kunjungans', 'unique_pasien_per_hari')) {
            Schema::table('kunjungans', function (Blueprint $table) {
                $table->dropUnique('unique_pasien_per_hari');
            });
        }

        if (! $this->hasIndex('kunjungans', 'unique_pasien_kunjungan_aktif_per_hari')) {
            Schema::table('kunjungans', function (Blueprint $table) {
                $table->unique(['id_pasien', 'active_tgl_kunjungan'], 'unique_pasien_kunjungan_aktif_per_hari');
            });
        }
    }

    public function down(): void
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropUnique('unique_pasien_kunjungan_aktif_per_hari');
            $table->dropColumn('active_tgl_kunjungan');
        });
    }

    private function hasIndex(string $table, string $name): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $index): bool => $index['name'] === $name);
    }
};
