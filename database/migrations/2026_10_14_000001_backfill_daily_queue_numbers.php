<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Isi nomor antrean harian untuk kunjungan yang sudah ada.
     *
     * Data diproses per bagian agar migration tetap aman ketika tabel sudah
     * berisi banyak kunjungan. Urutan tanggal, poli, dan ID mempertahankan
     * urutan pendaftaran yang sudah tercatat.
     */
    public function up(): void
    {
        $kodePoli = [
            'Poli Umum' => 'A',
            'Poli Gigi' => 'G',
            'Poli Anak' => 'C',
            'Poli Spesialis Kandungan' => 'K',
        ];

        $nomorBerikutnya = [];

        DB::table('kunjungans')
            ->orderBy('tgl_kunjungan')
            ->orderBy('poli_tujuan')
            ->orderBy('id_kunjungan')
            ->chunk(500, function ($kunjungans) use (&$nomorBerikutnya, $kodePoli): void {
                foreach ($kunjungans as $kunjungan) {
                    $kode = $kodePoli[$kunjungan->poli_tujuan] ?? null;

                    // Poli lama yang tidak dikenal dibiarkan apa adanya agar
                    // data tidak diberi kode yang keliru secara diam-diam.
                    if ($kode === null) {
                        continue;
                    }

                    $kunci = $kunjungan->tgl_kunjungan . '|' . $kunjungan->poli_tujuan;
                    $nomorBerikutnya[$kunci] = ($nomorBerikutnya[$kunci] ?? 0) + 1;

                    if ($kunjungan->kode_antrean !== null || $kunjungan->nomor_antrean !== null) {
                        continue;
                    }

                    DB::table('kunjungans')
                        ->where('id_kunjungan', $kunjungan->id_kunjungan)
                        ->whereNull('kode_antrean')
                        ->whereNull('nomor_antrean')
                        ->update([
                            'kode_antrean' => $kode,
                            'nomor_antrean' => $nomorBerikutnya[$kunci],
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Data historis tidak dikembalikan menjadi NULL secara otomatis.
        // Menghapus nomor yang sudah dipakai dapat merusak referensi antrean.
    }
};
