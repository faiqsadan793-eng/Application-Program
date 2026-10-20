<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllPoliEndToEndFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Memastikan alur pelayanan lengkap berjalan untuk setiap poli yang tersedia.
     * Skenario ini menggunakan database testing dan tidak menyentuh data aplikasi.
     */
    public function test_registration_examination_and_payment_work_for_all_poli(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $poliList = array_values(config('clinic.poli'));

        foreach ($poliList as $index => $poli) {
            $doctorUser = User::factory()->create([
                'role' => 'dokter',
                'name' => 'dr. Test ' . ($index + 1),
            ]);
            Dokter::create([
                'user_id' => $doctorUser->id,
                'nip_sip' => 'SIP-TEST-' . ($index + 1),
                'poli' => $poli,
            ]);

            $this->actingAs($staff)->post(route('pasien.store'), [
                'nama' => 'Pasien ' . ($index + 1),
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => $index % 2 === 0 ? 'Laki-laki' : 'Perempuan',
                'no_hp' => '0812345678' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'nik' => '327301010190' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'poli_tujuan' => $poli,
            ])->assertRedirect(route('pasien.index'));

            $pasien = Pasien::latest('id_pasien')->firstOrFail();
            $kunjungan = $pasien->kunjungans()->latest('id_kunjungan')->firstOrFail();

            $this->assertSame(Kunjungan::STATUS_ANTRE, $kunjungan->status);
            $this->assertSame('001', str_pad((string) $kunjungan->nomor_antrean, 3, '0', STR_PAD_LEFT));

            $this->actingAs($doctorUser)
                ->post(route('rekam-medis.mulai', $kunjungan))
                ->assertSessionHas('success');

            $medicalRecord = [
                'id_kunjungan' => $kunjungan->id_kunjungan,
                'keluhan' => 'Keluhan pengujian ' . $poli,
                'diagnosa' => 'Diagnosa pengujian ' . $poli,
                'resep_obat' => 'Resep pengujian ' . $poli,
            ];
            if ($poli === 'Poli Spesialis Kandungan') {
                $medicalRecord['hasil_pemeriksaan'] = 'Hasil pemeriksaan kandungan pengujian';
            }

            $this->actingAs($doctorUser)
                ->post(route('rekam-medis.store'), $medicalRecord)
                ->assertSessionHas('success');

            $transaksi = Transaksi::where('id_kunjungan', $kunjungan->id_kunjungan)->firstOrFail();
            $this->assertSame(Kunjungan::STATUS_SIAP_BAYAR, $kunjungan->fresh()->status);

            $this->actingAs($staff)->put(route('transaksi.update', $transaksi), [
                'biaya_tindakan' => 50000,
                'biaya_obat' => 10000,
                'uang_dibayar' => 100000,
                'metode_pembayaran' => 'cash',
            ])->assertSessionHas('success');

            $this->assertDatabaseHas('transaksis', [
                'id_transaksi' => $transaksi->id_transaksi,
                'id_kunjungan' => $kunjungan->id_kunjungan,
                'total_biaya' => 60000,
                'kembalian' => 40000,
                'status_pembayaran' => Transaksi::STATUS_LUNAS,
            ]);
            $this->assertSame(Kunjungan::STATUS_SELESAI, $kunjungan->fresh()->status);
        }
    }
}
