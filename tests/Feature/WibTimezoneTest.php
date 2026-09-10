<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WibTimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_examination_and_payment_use_the_same_wib_day(): void
    {
        // UTC masih 9 September, tetapi WIB sudah 10 September pukul 00.30.
        $this->travelTo(Carbon::parse('2026-09-09 17:30:00', 'UTC'));

        $staff = User::factory()->create(['role' => 'staff']);
        $doctor = User::factory()->create(['role' => 'dokter']);
        Dokter::create([
            'user_id' => $doctor->id,
            'nip_sip' => 'SIP-WIB-001',
            'poli' => 'Poli Umum',
        ]);

        $this->actingAs($staff)->post(route('pasien.store'), [
            'nama' => 'Pasien Uji WIB',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Perempuan',
            'no_hp' => '081234567890',
            'poli_tujuan' => 'Poli Umum',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $visit = Kunjungan::sole();
        $this->assertSame('2026-09-10', $visit->tgl_kunjungan->toDateString());
        $this->assertSame('2026-09-10', $visit->active_tgl_kunjungan->toDateString());
        $this->assertSame('2026-09-10 00:30:00', $visit->created_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-10 00:30:00', $visit->pasien->created_at->format('Y-m-d H:i:s'));

        $this->get(route('dashboard'))->assertOk()->assertViewHas('antreanAktif', 1)
            ->assertSee('10 September 2026');
        $this->get(route('kunjungan.index', ['rentang' => 'bulan']))->assertOk()
            ->assertViewHas('kunjungans', fn ($visits) => $visits->total() === 1);

        $this->actingAs($doctor)->get(route('rekam-medis.index'))->assertOk()
            ->assertSee('Pasien Uji WIB')->assertSee('00:30 WIB');
        $this->post(route('rekam-medis.mulai', $visit))->assertSessionHas('success');
        $this->post(route('rekam-medis.store'), [
            'id_kunjungan' => $visit->id_kunjungan,
            'keluhan' => 'Keluhan uji',
            'diagnosa' => 'Diagnosa uji',
            'resep_obat' => 'Resep uji',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame('2026-09-10 00:30:00', $visit->fresh()->rekamMedis->created_at->format('Y-m-d H:i:s'));
        $payment = Transaksi::sole();
        $this->actingAs($staff)->put(route('transaksi.update', $payment), [
            'biaya_tindakan' => 50000,
            'biaya_obat' => 10000,
            'uang_dibayar' => 100000,
            'metode_pembayaran' => 'cash',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame(Kunjungan::STATUS_SELESAI, $visit->fresh()->status);
        $this->assertSame('2026-09-10 00:30:00', $payment->fresh()->updated_at->format('Y-m-d H:i:s'));
        $this->get(route('riwayat-transaksi.index', [
            'tanggal_dari' => '2026-09-10',
            'tanggal_sampai' => '2026-09-10',
        ]))->assertOk()->assertViewHas('jumlahTransaksi', 1)
            ->assertViewHas('totalPendapatan', fn ($total) => (float) $total === 60000.0);
        $this->get(route('riwayat-transaksi.index', [
            'tanggal_dari' => '2026-09-09',
            'tanggal_sampai' => '2026-09-09',
        ]))->assertOk()->assertViewHas('jumlahTransaksi', 0);
    }

    public function test_daily_queue_changes_at_wib_midnight_instead_of_utc_midnight(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 16:59:59', 'UTC'));
        $staff = User::factory()->create(['role' => 'staff']);
        $this->actingAs($staff)->post(route('pasien.store'), [
            'nama' => 'Pasien Sebelum Tengah Malam',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'no_hp' => '081234567891',
            'poli_tujuan' => 'Poli Umum',
        ])->assertSessionHas('success');
        $previousVisit = Kunjungan::sole();
        $this->get(route('dashboard'))->assertOk()->assertViewHas('antreanAktif', 1);

        $this->travelTo(Carbon::parse('2026-09-09 17:00:00', 'UTC'));
        $this->get(route('dashboard'))->assertOk()->assertViewHas('antreanAktif', 0);
        $this->post(route('kunjungan.store'), [
            'id_pasien' => $previousVisit->id_pasien,
            'poli_tujuan' => 'Poli Umum',
        ])->assertSessionHas('success');

        $this->assertSame('2026-09-09', $previousVisit->fresh()->tgl_kunjungan->toDateString());
        $newVisit = Kunjungan::where('id_kunjungan', '!=', $previousVisit->id_kunjungan)->sole();
        $this->assertSame('2026-09-10', $newVisit->tgl_kunjungan->toDateString());
        $this->assertSame('2026-09-10', $newVisit->active_tgl_kunjungan->toDateString());
        $this->get(route('dashboard'))->assertOk()->assertViewHas('antreanAktif', 1);
    }
}
