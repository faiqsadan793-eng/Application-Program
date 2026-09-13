<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScalabilityGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_busy_pages_remain_bounded_with_one_thousand_daily_visits(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $now = now();

        foreach (array_chunk(range(1, 1000), 200) as $ids) {
            DB::table('pasiens')->insert(array_map(fn (int $id): array => [
                'id_pasien' => $id + 100,
                'nama' => "Pasien Beban {$id}",
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'Laki-laki',
                'no_hp' => '08'.str_pad((string) $id, 10, '0', STR_PAD_LEFT),
                'created_at' => $now,
                'updated_at' => $now,
            ], $ids));

            DB::table('kunjungans')->insert(array_map(fn (int $id): array => [
                'id_kunjungan' => $id,
                'id_pasien' => $id + 100,
                'tgl_kunjungan' => $now->toDateString(),
                'active_tgl_kunjungan' => $now->toDateString(),
                'masuk_antrean_pada' => $now->copy()->addMicroseconds($id),
                'status' => 'siap_bayar',
                'poli_tujuan' => 'Poli Umum',
                'created_at' => $now,
                'updated_at' => $now,
            ], $ids));
        }

        foreach (array_chunk(range(1, 1000), 200) as $ids) {
            DB::table('transaksis')->insert(array_map(fn (int $id): array => [
                'id_transaksi' => $id,
                'id_kunjungan' => $id,
                'total_biaya' => 0,
                'status_pembayaran' => 'belum',
                'created_at' => $now,
                'updated_at' => $now,
            ], $ids));
        }

        $this->actingAs($staff)->get(route('dashboard'))->assertOk()
            ->assertViewHas('antreanHariIni', fn ($items): bool => $items->count() === 100);
        $this->get(route('transaksi.index'))->assertOk()
            ->assertViewHas('transaksis', fn ($items): bool => $items->count() === 25 && $items->total() === 1000);

        $visitIndexes = collect(Schema::getIndexes('kunjungans'))->pluck('name');
        $transactionIndexes = collect(Schema::getIndexes('transaksis'))->pluck('name');
        $this->assertContains('kunjungan_antrean_poli_harian_index', $visitIndexes);
        $this->assertContains('transaksi_status_dibuat_index', $transactionIndexes);
    }
}
