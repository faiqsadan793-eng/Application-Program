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
        foreach (config('clinic.poli') as $index => $poli) {
            $doctor = User::create([
                'name' => 'Dokter ' . $poli,
                'email' => 'dokter.' . ($index === 'Poli Spesialis Kandungan' ? 'kandungan' : strtolower(str_replace('Poli ', '', $index))) . '.browser@test.local',
                'password' => $password,
                'role' => 'dokter',
            ]);
            Dokter::create(['user_id' => $doctor->id, 'nip_sip' => 'SIP-BROWSER-' . $index, 'poli' => $poli]);
        }

        $medicalPatient = Pasien::create($this->patient('Pasien Rekam Medis', '081200000001'));
        $this->visit($medicalPatient, 'Poli Umum', Kunjungan::STATUS_MENUNGGU_DOKTER);

        $paymentPatient = Pasien::create($this->patient('Pasien Pembayaran', '081200000002'));
        $paymentVisit = $this->visit($paymentPatient, 'Poli Gigi', Kunjungan::STATUS_SIAP_BAYAR);
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

        $childPatient = Pasien::create($this->patient('Pasien Anak', '081200000003'));
        $this->visit($childPatient, 'Poli Anak', Kunjungan::STATUS_ANTRE);

        $obstetricPatient = Pasien::create($this->patient('Pasien Kandungan', '081200000004'));
        $this->visit($obstetricPatient, 'Poli Spesialis Kandungan', Kunjungan::STATUS_MENUNGGU_DOKTER);
    }

    private function patient(string $name, string $phone): array
    {
        return ['nama' => $name, 'tanggal_lahir' => '1990-01-01', 'jenis_kelamin' => 'Perempuan', 'no_hp' => $phone];
    }

    private function visit(Pasien $patient, string $poli, string $status): Kunjungan
    {
        $date = Kunjungan::tanggalHariIni();

        return Kunjungan::create([
            'id_pasien' => $patient->id_pasien,
            'tgl_kunjungan' => $date,
            'poli_tujuan' => $poli,
            'kode_antrean' => Kunjungan::kodeAntreanUntuk($poli),
            'nomor_antrean' => Kunjungan::nomorAntreanBerikutnya($date, $poli),
            'status' => $status,
        ]);
    }
}
