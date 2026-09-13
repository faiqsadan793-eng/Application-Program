<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\RekamMedis;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class BrowserTestSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('testing')) {
            throw new RuntimeException('BrowserTestSeeder hanya boleh dijalankan pada APP_ENV=testing.');
        }

        $password = Hash::make('browser-test-password');
        $staff = User::create(['name' => 'Staff Browser', 'email' => 'staff.browser@test.local', 'password' => $password, 'role' => 'staff']);
        $doctor = User::create(['name' => 'Dokter Browser', 'email' => 'dokter.browser@test.local', 'password' => $password, 'role' => 'dokter']);
        Dokter::create(['user_id' => $doctor->id, 'nip_sip' => 'SIP-BROWSER', 'poli' => 'Poli Umum']);

        $medicalPatient = Pasien::create($this->patient('Pasien Rekam Medis', '081200000001'));
        Kunjungan::create([
            'id_pasien' => $medicalPatient->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_MENUNGGU_DOKTER,
        ]);

        $paymentPatient = Pasien::create($this->patient('Pasien Pembayaran', '081200000002'));
        $paymentVisit = Kunjungan::create([
            'id_pasien' => $paymentPatient->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Gigi',
            'status' => Kunjungan::STATUS_SIAP_BAYAR,
        ]);
        RekamMedis::create([
            'id_kunjungan' => $paymentVisit->id_kunjungan,
            'nama_dokter' => 'Dokter Browser',
            'keluhan' => 'Keluhan pengujian',
            'diagnosa' => 'Diagnosis pengujian',
            'resep_obat' => 'Resep pengujian',
        ]);
        Transaksi::create([
            'id_kunjungan' => $paymentVisit->id_kunjungan,
            'total_biaya' => 0,
            'status_pembayaran' => Transaksi::STATUS_BELUM,
        ]);
    }

    private function patient(string $name, string $phone): array
    {
        return ['nama' => $name, 'tanggal_lahir' => '1990-01-01', 'jenis_kelamin' => 'Perempuan', 'no_hp' => $phone];
    }
}
