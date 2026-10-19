<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\RekamMedis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoliSpecificFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_poli_kandungan_saves_obstetric_notes_and_vital_signs(): void
    {
        [$doctorUser, $doctor] = $this->doctor('Poli Spesialis Kandungan');
        $visit = $this->visit('Poli Spesialis Kandungan');

        $this->actingAs($doctorUser)->post(route('rekam-medis.store'), [
            'id_kunjungan' => $visit->id_kunjungan,
            'keluhan' => 'Kontrol rutin',
            'suhu_tubuh' => '36.5',
            'tinggi_badan' => '165',
            'berat_badan' => '60',
            'tekanan_darah' => '120/80',
            'hasil_pemeriksaan' => 'Kehamilan berkembang baik',
            'diagnosa' => 'Kehamilan trimester kedua',
            'resep_obat' => 'Vitamin kehamilan',
            'catatan_tambahan' => 'Kontrol kembali dua minggu lagi',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('rekam_medises', [
            'id_kunjungan' => $visit->id_kunjungan,
            'id_dokter' => $doctor->id_dokter,
            'hasil_pemeriksaan' => 'Kehamilan berkembang baik',
            'catatan_tambahan' => 'Kontrol kembali dua minggu lagi',
            'suhu_tubuh' => 36.5,
            'tekanan_darah' => '120/80',
        ]);
    }

    public function test_poli_kandungan_requires_hasil_pemeriksaan(): void
    {
        [$doctorUser] = $this->doctor('Poli Spesialis Kandungan');
        $visit = $this->visit('Poli Spesialis Kandungan');

        $this->actingAs($doctorUser)->post(route('rekam-medis.store'), [
            'id_kunjungan' => $visit->id_kunjungan,
            'keluhan' => 'Kontrol rutin',
            'diagnosa' => 'Kehamilan normal',
            'resep_obat' => 'Tidak ada',
        ])->assertSessionHasErrors('hasil_pemeriksaan');

        $this->assertDatabaseMissing('rekam_medises', ['id_kunjungan' => $visit->id_kunjungan]);
        $this->assertSame(Kunjungan::STATUS_MENUNGGU_DOKTER, $visit->fresh()->status);
    }

    public function test_poli_anak_saves_general_record_without_obstetric_fields(): void
    {
        [$doctorUser] = $this->doctor('Poli Anak');
        $visit = $this->visit('Poli Anak');

        $this->actingAs($doctorUser)->post(route('rekam-medis.store'), [
            'id_kunjungan' => $visit->id_kunjungan,
            'keluhan' => 'Demam sejak kemarin',
            'diagnosa' => 'Infeksi saluran napas',
            'resep_obat' => 'Paracetamol sesuai dosis',
        ])->assertSessionHas('success');

        $record = RekamMedis::where('id_kunjungan', $visit->id_kunjungan)->firstOrFail();
        $this->assertSame('Demam sejak kemarin', $record->keluhan);
        $this->assertNull($record->hasil_pemeriksaan);
        $this->assertNull($record->catatan_tambahan);
    }

    public function test_poli_gigi_saves_general_record_and_does_not_store_obstetric_fields(): void
    {
        [$doctorUser] = $this->doctor('Poli Gigi');
        $visit = $this->visit('Poli Gigi');

        $this->actingAs($doctorUser)->post(route('rekam-medis.store'), [
            'id_kunjungan' => $visit->id_kunjungan,
            'keluhan' => 'Gigi berlubang',
            'diagnosa' => 'Karies gigi',
            'resep_obat' => 'Obat pereda nyeri',
            'hasil_pemeriksaan' => 'Field khusus kandungan tidak boleh tersimpan',
            'catatan_tambahan' => 'Catatan khusus kandungan tidak boleh tersimpan',
        ])->assertSessionHas('success');

        $record = RekamMedis::where('id_kunjungan', $visit->id_kunjungan)->firstOrFail();
        $this->assertNull($record->hasil_pemeriksaan);
        $this->assertNull($record->catatan_tambahan);
    }

    /** @return array{0: User, 1: Dokter} */
    private function doctor(string $poli): array
    {
        $user = User::factory()->create(['role' => 'dokter', 'name' => 'dr. Uji ' . $poli]);
        $doctor = Dokter::create([
            'user_id' => $user->id,
            'nip_sip' => 'SIP-' . md5($poli),
            'poli' => $poli,
        ]);

        return [$user, $doctor];
    }

    private function visit(string $poli): Kunjungan
    {
        $patient = Pasien::create([
            'nama' => 'Pasien Uji ' . $poli,
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Perempuan',
            'no_hp' => '081234567890',
        ]);

        return Kunjungan::create([
            'id_pasien' => $patient->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => $poli,
            'status' => Kunjungan::STATUS_MENUNGGU_DOKTER,
        ]);
    }
}
