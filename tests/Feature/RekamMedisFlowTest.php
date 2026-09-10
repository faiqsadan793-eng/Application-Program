<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RekamMedisFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_patient_receives_a_permanent_medical_record_number(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->post(route('pasien.store'), [
            'nama' => 'Siti Aminah',
            'tanggal_lahir' => '1995-02-12',
            'jenis_kelamin' => 'Perempuan',
            'no_hp' => '081234567890',
            'nik' => '3273011202950001',
            'poli_tujuan' => 'Poli Umum',
        ])->assertRedirect(route('pasien.index'));

        $pasien = Pasien::firstOrFail();
        $this->assertSame(Pasien::nomorRekamMedisUntuk($pasien->id_pasien), $pasien->no_rm);
        $this->assertDatabaseHas('kunjungans', ['id_pasien' => $pasien->id_pasien, 'status' => Kunjungan::STATUS_ANTRE]);
    }

    public function test_doctor_can_save_record_only_for_own_poli_and_active_visit(): void
    {
        [$dokterUser, $dokter] = $this->dokter('Poli Umum');
        $pasien = Pasien::create($this->pasienData());
        $kunjungan = Kunjungan::create([
            'id_pasien' => $pasien->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_MENUNGGU_DOKTER,
        ]);

        $this->actingAs($dokterUser)->post(route('rekam-medis.store'), [
            'id_kunjungan' => $kunjungan->id_kunjungan,
            'keluhan' => 'Demam dua hari',
            'diagnosa' => 'Influenza',
            'resep_obat' => 'Paracetamol',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('rekam_medises', [
            'id_kunjungan' => $kunjungan->id_kunjungan,
            'id_dokter' => $dokter->id_dokter,
            'nama_dokter' => $dokterUser->name,
        ]);
        $this->assertDatabaseHas('kunjungans', ['id_kunjungan' => $kunjungan->id_kunjungan, 'status' => Kunjungan::STATUS_SIAP_BAYAR]);
        $this->assertDatabaseHas('transaksis', ['id_kunjungan' => $kunjungan->id_kunjungan, 'status_pembayaran' => 'belum']);
    }

    public function test_invalid_medical_record_keeps_doctor_input_and_does_not_finish_visit(): void
    {
        [$dokterUser] = $this->dokter('Poli Umum');
        $pasien = Pasien::create($this->pasienData());
        $kunjungan = Kunjungan::create([
            'id_pasien' => $pasien->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_MENUNGGU_DOKTER,
        ]);

        $this->actingAs($dokterUser)->post(route('rekam-medis.store'), [
            'id_kunjungan' => $kunjungan->id_kunjungan,
            'keluhan' => str_repeat('K', 2001),
            'diagnosa' => 'Diagnosa yang harus tetap tampil',
            'resep_obat' => 'Resep yang harus tetap tampil',
        ])->assertSessionHasErrors('keluhan')
            ->assertSessionHasInput('diagnosa', 'Diagnosa yang harus tetap tampil')
            ->assertSessionHasInput('resep_obat', 'Resep yang harus tetap tampil')
            ->assertSessionHasInput('id_kunjungan', $kunjungan->id_kunjungan);

        $this->assertDatabaseMissing('rekam_medises', ['id_kunjungan' => $kunjungan->id_kunjungan]);
        $this->assertDatabaseMissing('transaksis', ['id_kunjungan' => $kunjungan->id_kunjungan]);
        $this->assertSame(Kunjungan::STATUS_MENUNGGU_DOKTER, $kunjungan->fresh()->status);
    }

    public function test_doctor_cannot_start_or_save_another_poli_visit(): void
    {
        [$dokterUser] = $this->dokter('Poli Umum');
        $pasien = Pasien::create($this->pasienData());
        $kunjungan = Kunjungan::create([
            'id_pasien' => $pasien->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Gigi',
            'status' => Kunjungan::STATUS_ANTRE,
        ]);

        $this->actingAs($dokterUser)->post(route('rekam-medis.mulai', $kunjungan))->assertNotFound();
        $this->actingAs($dokterUser)->post(route('rekam-medis.store'), [
            'id_kunjungan' => $kunjungan->id_kunjungan,
            'keluhan' => 'Keluhan',
            'diagnosa' => 'Diagnosa',
            'resep_obat' => 'Resep',
        ])->assertNotFound();
        $this->assertDatabaseHas('kunjungans', ['id_kunjungan' => $kunjungan->id_kunjungan, 'status' => Kunjungan::STATUS_ANTRE]);
        $this->assertDatabaseMissing('rekam_medises', ['id_kunjungan' => $kunjungan->id_kunjungan]);
    }

    public function test_doctor_must_start_a_visit_before_saving_the_medical_record(): void
    {
        [$dokterUser] = $this->dokter('Poli Umum');
        $pasien = Pasien::create($this->pasienData());
        $kunjungan = Kunjungan::create([
            'id_pasien' => $pasien->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_ANTRE,
        ]);

        $this->actingAs($dokterUser)->post(route('rekam-medis.store'), [
            'id_kunjungan' => $kunjungan->id_kunjungan,
            'keluhan' => 'Keluhan',
            'diagnosa' => 'Diagnosa',
            'resep_obat' => 'Resep',
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('rekam_medises', ['id_kunjungan' => $kunjungan->id_kunjungan]);
        $this->assertDatabaseHas('kunjungans', ['id_kunjungan' => $kunjungan->id_kunjungan, 'status' => Kunjungan::STATUS_ANTRE]);
    }

    public function test_doctor_cannot_call_a_second_patient_while_another_visit_is_being_examined(): void
    {
        [$dokterUser] = $this->dokter('Poli Umum');
        $pasienPertama = Pasien::create($this->pasienData());
        $pasienKedua = Pasien::create(array_replace($this->pasienData(), ['no_hp' => '081234567892']));
        $kunjunganPertama = Kunjungan::create([
            'id_pasien' => $pasienPertama->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_ANTRE,
        ]);
        $kunjunganKedua = Kunjungan::create([
            'id_pasien' => $pasienKedua->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_ANTRE,
        ]);

        $this->actingAs($dokterUser)->post(route('rekam-medis.panggil-selanjutnya'))
            ->assertSessionHas('success');
        $this->actingAs($dokterUser)->post(route('rekam-medis.panggil-selanjutnya'))
            ->assertSessionHas('info');

        $this->assertDatabaseHas('kunjungans', ['id_kunjungan' => $kunjunganPertama->id_kunjungan, 'status' => Kunjungan::STATUS_MENUNGGU_DOKTER]);
        $this->assertDatabaseHas('kunjungans', ['id_kunjungan' => $kunjunganKedua->id_kunjungan, 'status' => Kunjungan::STATUS_ANTRE]);
    }

    public function test_started_examination_can_be_completed_after_wib_midnight_without_carrying_old_queue(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 23:59:59', 'Asia/Jakarta'));
        [$dokterUser] = $this->dokter('Poli Umum');

        $pasienAktif = Pasien::create($this->pasienData());
        $pasienBelumDimulai = Pasien::create(array_replace($this->pasienData(), [
            'nama' => 'Pasien Lama Belum Dimulai',
            'no_hp' => '081234567892',
        ]));
        $pasienPoliLain = Pasien::create(array_replace($this->pasienData(), [
            'nama' => 'Pasien Poli Lain',
            'no_hp' => '081234567893',
        ]));

        $pemeriksaanAktif = Kunjungan::create([
            'id_pasien' => $pasienAktif->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_MENUNGGU_DOKTER,
        ]);
        $antreanLama = Kunjungan::create([
            'id_pasien' => $pasienBelumDimulai->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_ANTRE,
        ]);
        $pemeriksaanPoliLain = Kunjungan::create([
            'id_pasien' => $pasienPoliLain->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Gigi',
            'status' => Kunjungan::STATUS_MENUNGGU_DOKTER,
        ]);

        $this->travelTo(Carbon::parse('2026-09-11 00:00:01', 'Asia/Jakarta'));

        $this->actingAs($dokterUser)->get(route('rekam-medis.index'))
            ->assertOk()
            ->assertSee($pasienAktif->nama)
            ->assertDontSee($pasienBelumDimulai->nama)
            ->assertDontSee($pasienPoliLain->nama);

        $rekamMedisTidakValid = [
            'keluhan' => 'Tidak boleh diproses',
            'diagnosa' => 'Tidak boleh diproses',
            'resep_obat' => 'Tidak boleh diproses',
        ];
        $this->post(route('rekam-medis.store'), [
            'id_kunjungan' => $antreanLama->id_kunjungan,
            ...$rekamMedisTidakValid,
        ])->assertNotFound();
        $this->post(route('rekam-medis.store'), [
            'id_kunjungan' => $pemeriksaanPoliLain->id_kunjungan,
            ...$rekamMedisTidakValid,
        ])->assertNotFound();
        $this->assertDatabaseMissing('rekam_medises', ['id_kunjungan' => $antreanLama->id_kunjungan]);
        $this->assertDatabaseMissing('transaksis', ['id_kunjungan' => $antreanLama->id_kunjungan]);

        $pasienHariIni = Pasien::create(array_replace($this->pasienData(), [
            'nama' => 'Pasien Hari Ini',
            'no_hp' => '081234567894',
        ]));
        $antreanHariIni = Kunjungan::create([
            'id_pasien' => $pasienHariIni->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_ANTRE,
        ]);

        $this->post(route('rekam-medis.panggil-selanjutnya'))->assertSessionHas('info');
        $this->post(route('rekam-medis.mulai', $antreanHariIni))->assertSessionHas('error');
        $this->assertSame(Kunjungan::STATUS_ANTRE, $antreanHariIni->fresh()->status);

        $this->post(route('rekam-medis.store'), [
            'id_kunjungan' => $pemeriksaanAktif->id_kunjungan,
            'keluhan' => 'Keluhan lintas hari',
            'diagnosa' => 'Diagnosa lintas hari',
            'resep_obat' => 'Resep lintas hari',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertDatabaseHas('rekam_medises', ['id_kunjungan' => $pemeriksaanAktif->id_kunjungan]);
        $this->assertDatabaseHas('transaksis', [
            'id_kunjungan' => $pemeriksaanAktif->id_kunjungan,
            'status_pembayaran' => 'belum',
        ]);
        $this->assertSame(Kunjungan::STATUS_ANTRE, $antreanLama->fresh()->status);
        $this->assertSame(Kunjungan::STATUS_MENUNGGU_DOKTER, $pemeriksaanPoliLain->fresh()->status);

        $this->post(route('rekam-medis.store'), [
            'id_kunjungan' => $pemeriksaanAktif->id_kunjungan,
            'keluhan' => 'Tidak boleh tersimpan lagi',
            'diagnosa' => 'Tidak boleh tersimpan lagi',
            'resep_obat' => 'Tidak boleh tersimpan lagi',
        ])->assertNotFound();

        $this->post(route('rekam-medis.panggil-selanjutnya'))->assertSessionHas('success');
        $this->assertSame(Kunjungan::STATUS_MENUNGGU_DOKTER, $antreanHariIni->fresh()->status);
    }

    public function test_staff_can_view_patient_medical_record_index_and_detail(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $pasien = Pasien::create($this->pasienData());
        $pasien->no_rm = Pasien::nomorRekamMedisUntuk($pasien->id_pasien);
        $pasien->save();
        $kunjungan = Kunjungan::create([
            'id_pasien' => $pasien->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_SELESAI,
        ]);
        \App\Models\RekamMedis::create([
            'id_kunjungan' => $kunjungan->id_kunjungan,
            'nama_dokter' => 'dr. Ratna',
            'keluhan' => 'Batuk',
            'diagnosa' => 'ISPA',
            'resep_obat' => 'Obat batuk',
        ]);

        $this->actingAs($staff)->get(route('rekam-medis-pasien.index', ['search' => $pasien->no_rm]))
            ->assertOk()->assertSee($pasien->no_rm)->assertSee('ISPA');
        $this->actingAs($staff)->get(route('rekam-medis-pasien.show', $pasien))
            ->assertOk()->assertSee('Riwayat Pemeriksaan')->assertSee('Obat batuk');
    }

    /** @return array{0: User, 1: Dokter} */
    private function dokter(string $poli): array
    {
        $user = User::factory()->create(['role' => 'dokter', 'name' => 'dr. Ratna']);
        $dokter = Dokter::create(['user_id' => $user->id, 'nip_sip' => 'SIP-' . $poli, 'poli' => $poli]);

        return [$user, $dokter];
    }

    /** @return array<string, string> */
    private function pasienData(): array
    {
        return [
            'nama' => 'Budi Santoso',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'no_hp' => '081234567891',
        ];
    }
}
