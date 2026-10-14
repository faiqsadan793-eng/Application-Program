<?php

namespace Tests\Feature;

use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KunjunganFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_cannot_create_a_second_visit_while_the_first_visit_is_active(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $pasien = $this->pasien();
        Kunjungan::create([
            'id_pasien' => $pasien->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_SIAP_BAYAR,
        ]);

        $this->actingAs($staff)->post(route('kunjungan.store'), [
            'id_pasien' => $pasien->id_pasien,
            'poli_tujuan' => 'Poli Gigi',
        ])->assertSessionHas('error');

        $this->assertSame(1, Kunjungan::where('id_pasien', $pasien->id_pasien)->count());
    }

    public function test_patient_can_create_a_new_visit_after_a_same_day_visit_is_completed(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $pasien = $this->pasien();
        $kunjunganSelesai = Kunjungan::create([
            'id_pasien' => $pasien->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_SELESAI,
        ]);

        $this->actingAs($staff)->post(route('kunjungan.store'), [
            'id_pasien' => $pasien->id_pasien,
            'poli_tujuan' => 'Poli Gigi',
        ])->assertSessionHas('success');

        $this->assertNull($kunjunganSelesai->fresh()->active_tgl_kunjungan);
        $this->assertTrue(Kunjungan::query()
            ->where('id_pasien', $pasien->id_pasien)
            ->where('poli_tujuan', 'Poli Gigi')
            ->where('status', Kunjungan::STATUS_ANTRE)
            ->whereDate('active_tgl_kunjungan', now()->toDateString())
            ->exists());
    }

    public function test_daily_queue_numbers_restart_per_poli_and_date(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $this->travelTo(now()->setTime(8, 0));

        $register = function (string $name, string $phone, string $poli) use ($staff): Kunjungan {
            $this->actingAs($staff)->post(route('pasien.store'), [
                'nama' => $name,
                'tanggal_lahir' => '1995-02-12',
                'jenis_kelamin' => 'Perempuan',
                'no_hp' => $phone,
                'poli_tujuan' => $poli,
            ])->assertRedirect(route('pasien.index'));

            return Kunjungan::latest('id_kunjungan')->firstOrFail();
        };

        $umumPertama = $register('Pasien Umum 1', '081234567801', 'Poli Umum');
        $umumKedua = $register('Pasien Umum 2', '081234567802', 'Poli Umum');
        $gigiPertama = $register('Pasien Gigi 1', '081234567803', 'Poli Gigi');

        $this->travelTo(now()->addDay()->setTime(8, 0));
        $umumHariBerikutnya = $register('Pasien Umum Hari Berikutnya', '081234567804', 'Poli Umum');

        $this->assertSame(['A', 1], [$umumPertama->kode_antrean, $umumPertama->nomor_antrean]);
        $this->assertSame(['A', 2], [$umumKedua->kode_antrean, $umumKedua->nomor_antrean]);
        $this->assertSame(['G', 1], [$gigiPertama->kode_antrean, $gigiPertama->nomor_antrean]);
        $this->assertSame(['A', 1], [$umumHariBerikutnya->kode_antrean, $umumHariBerikutnya->nomor_antrean]);
    }

    private function pasien(): Pasien
    {
        return Pasien::create([
            'nama' => 'Siti Aminah',
            'tanggal_lahir' => '1995-02-12',
            'jenis_kelamin' => 'Perempuan',
            'no_hp' => '081234567890',
        ]);
    }
}
