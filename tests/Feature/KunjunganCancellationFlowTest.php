<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KunjunganCancellationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_cancel_active_visit_without_deleting_history_and_register_again(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'name' => 'Staff Klinik']);
        $pasien = Pasien::create($this->pasienData());
        $kunjungan = $this->kunjungan($pasien, Kunjungan::STATUS_ANTRE);

        $this->actingAs($staff)->patch(route('kunjungan.batalkan', $kunjungan), [
            'kategori_pembatalan' => 'salah_poli',
            'alasan_pembatalan' => 'Pasien salah memilih poli',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $kunjungan->refresh();
        $this->assertSame(Kunjungan::STATUS_DIBATALKAN, $kunjungan->status);
        $this->assertSame('salah_poli', $kunjungan->kategori_pembatalan);
        $this->assertSame('Pasien salah memilih poli', $kunjungan->alasan_pembatalan);
        $this->assertSame($staff->id, $kunjungan->dibatalkan_oleh);
        $this->assertSame($staff->name, $kunjungan->nama_pembatal);
        $this->assertNotNull($kunjungan->dibatalkan_pada);
        $this->assertNull($kunjungan->active_tgl_kunjungan);
        $this->assertDatabaseHas('kunjungans', ['id_kunjungan' => $kunjungan->id_kunjungan]);

        $this->post(route('kunjungan.store'), [
            'id_pasien' => $pasien->id_pasien,
            'poli_tujuan' => 'Poli Gigi',
        ])->assertSessionHas('success');

        $this->assertSame(2, $pasien->kunjungans()->count());
        $this->get(route('kunjungan.index', ['status' => 'dibatalkan', 'rentang' => 'semua']))
            ->assertOk()->assertSee('Dibatalkan')->assertSee('Pasien Uji');
        $this->get(route('kunjungan.show', $kunjungan))
            ->assertOk()->assertSee('Pasien salah memilih poli')->assertSee('Staff Klinik');
    }

    public function test_doctor_can_return_own_patient_to_today_queue_and_cancel_no_show(): void
    {
        [$dokterUser] = $this->dokter('Poli Umum');
        $pasien = Pasien::create($this->pasienData());
        $kunjungan = $this->kunjungan($pasien, Kunjungan::STATUS_MENUNGGU_DOKTER);

        $this->actingAs($dokterUser)->patch(route('kunjungan.kembalikan-antrean', $kunjungan))
            ->assertSessionHas('success');
        $this->assertSame(Kunjungan::STATUS_ANTRE, $kunjungan->fresh()->status);

        $this->post(route('rekam-medis.mulai', $kunjungan))->assertSessionHas('success');
        $this->patch(route('kunjungan.batalkan', $kunjungan), [
            'kategori_pembatalan' => 'tidak_hadir',
            'alasan_pembatalan' => 'Pasien tidak hadir saat dipanggil',
        ])->assertSessionHas('success');

        $this->assertSame(Kunjungan::STATUS_DIBATALKAN, $kunjungan->fresh()->status);
        $this->assertDatabaseMissing('rekam_medises', ['id_kunjungan' => $kunjungan->id_kunjungan]);
        $this->assertDatabaseMissing('transaksis', ['id_kunjungan' => $kunjungan->id_kunjungan]);
    }

    public function test_doctor_cannot_change_another_poli_and_terminal_visits_cannot_be_cancelled(): void
    {
        [$dokterUser] = $this->dokter('Poli Umum');
        $pasien = Pasien::create($this->pasienData());
        $poliLain = $this->kunjungan($pasien, Kunjungan::STATUS_MENUNGGU_DOKTER, 'Poli Gigi');

        $this->actingAs($dokterUser)->patch(route('kunjungan.batalkan', $poliLain), [
            'kategori_pembatalan' => 'lainnya',
            'alasan_pembatalan' => 'Tidak berhak',
        ])->assertForbidden();
        $this->patch(route('kunjungan.kembalikan-antrean', $poliLain))->assertForbidden();
        $this->assertSame(Kunjungan::STATUS_MENUNGGU_DOKTER, $poliLain->fresh()->status);

        $staff = User::factory()->create(['role' => 'staff']);
        $kunjunganSelesai = $this->kunjungan(
            Pasien::create(array_replace($this->pasienData(), ['no_hp' => '081234567892'])),
            Kunjungan::STATUS_SELESAI,
        );
        $this->actingAs($staff)->patch(route('kunjungan.batalkan', $kunjunganSelesai), [
            'kategori_pembatalan' => 'lainnya',
            'alasan_pembatalan' => 'Tidak boleh',
        ])->assertSessionHas('error');
        $this->assertSame(Kunjungan::STATUS_SELESAI, $kunjunganSelesai->fresh()->status);
    }

    public function test_doctor_cannot_cancel_patient_who_has_not_been_called(): void
    {
        [$dokterUser] = $this->dokter('Poli Umum');
        $kunjungan = $this->kunjungan(
            Pasien::create($this->pasienData()),
            Kunjungan::STATUS_ANTRE,
        );

        $this->actingAs($dokterUser)->patch(route('kunjungan.batalkan', $kunjungan), [
            'kategori_pembatalan' => 'lainnya',
            'alasan_pembatalan' => 'Belum dipanggil',
        ])->assertSessionHas('error');

        $this->assertSame(Kunjungan::STATUS_ANTRE, $kunjungan->fresh()->status);
    }

    public function test_previous_day_examination_cannot_be_returned_to_new_day_queue(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 23:59:59', 'Asia/Jakarta'));
        [$dokterUser] = $this->dokter('Poli Umum');
        $kunjungan = $this->kunjungan(
            Pasien::create($this->pasienData()),
            Kunjungan::STATUS_MENUNGGU_DOKTER,
        );

        $this->travelTo(Carbon::parse('2026-09-11 00:00:01', 'Asia/Jakarta'));
        $this->actingAs($dokterUser)->patch(route('kunjungan.kembalikan-antrean', $kunjungan))
            ->assertSessionHas('error');

        $this->assertSame(Kunjungan::STATUS_MENUNGGU_DOKTER, $kunjungan->fresh()->status);
    }

    public function test_cancellation_reason_is_required(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $kunjungan = $this->kunjungan(Pasien::create($this->pasienData()), Kunjungan::STATUS_ANTRE);

        $this->actingAs($staff)->from(route('kunjungan.show', $kunjungan))
            ->patch(route('kunjungan.batalkan', $kunjungan), [
                'kategori_pembatalan' => 'lainnya',
                'alasan_pembatalan' => '',
            ])
            ->assertRedirect(route('kunjungan.show', $kunjungan))
            ->assertSessionHasErrors('alasan_pembatalan');

        $this->assertSame(Kunjungan::STATUS_ANTRE, $kunjungan->fresh()->status);
    }

    public function test_returned_patient_moves_to_the_back_of_same_poli_queue(): void
    {
        $this->travelTo(Carbon::parse('2026-09-13 08:00:00', 'Asia/Jakarta'));
        [$dokterUser] = $this->dokter('Poli Umum');
        $first = $this->kunjungan(Pasien::create($this->pasienData()), Kunjungan::STATUS_MENUNGGU_DOKTER);

        $this->travelTo(Carbon::parse('2026-09-13 08:05:00', 'Asia/Jakarta'));
        $second = $this->kunjungan(Pasien::create(array_replace($this->pasienData(), [
            'nama' => 'Pasien Kedua',
            'no_hp' => '081234567892',
        ])), Kunjungan::STATUS_ANTRE);

        $this->travelTo(Carbon::parse('2026-09-13 08:10:00', 'Asia/Jakarta'));
        $this->actingAs($dokterUser)->patch(route('kunjungan.kembalikan-antrean', $first))
            ->assertSessionHas('success');

        $first->refresh();
        $this->assertTrue($first->masuk_antrean_pada->greaterThan($second->masuk_antrean_pada));
        $this->post(route('rekam-medis.panggil-selanjutnya'))->assertSessionHas('success');
        $this->assertSame(Kunjungan::STATUS_MENUNGGU_DOKTER, $second->fresh()->status);
        $this->assertSame(Kunjungan::STATUS_ANTRE, $first->fresh()->status);
    }

    /** @return array{0: User, 1: Dokter} */
    private function dokter(string $poli): array
    {
        $user = User::factory()->create(['role' => 'dokter', 'name' => 'dr. Uji']);
        $dokter = Dokter::create(['user_id' => $user->id, 'nip_sip' => 'SIP-' . $poli, 'poli' => $poli]);

        return [$user, $dokter];
    }

    private function kunjungan(Pasien $pasien, string $status, string $poli = 'Poli Umum'): Kunjungan
    {
        return Kunjungan::create([
            'id_pasien' => $pasien->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => $poli,
            'status' => $status,
        ]);
    }

    /** @return array<string, string> */
    private function pasienData(): array
    {
        return [
            'nama' => 'Pasien Uji',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Perempuan',
            'no_hp' => '081234567891',
        ];
    }
}
