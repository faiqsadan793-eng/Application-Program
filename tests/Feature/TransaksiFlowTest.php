<?php

namespace Tests\Feature;

use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransaksiFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_totals_are_calculated_on_the_server(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $transaksi = $this->transaksiSiapBayar();

        $this->actingAs($staff)->put(route('transaksi.update', $transaksi), [
            'biaya_tindakan' => 50000,
            'biaya_obat' => 12500,
            'uang_dibayar' => 100000,
            'metode_pembayaran' => 'cash',
            // Nilai ini sengaja salah; server wajib mengabaikannya.
            'total_biaya' => 1,
            'kembalian' => 999999,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('transaksis', [
            'id_transaksi' => $transaksi->id_transaksi,
            'biaya_tindakan' => 50000,
            'biaya_obat' => 12500,
            'total_biaya' => 62500,
            'uang_dibayar' => 100000,
            'kembalian' => 37500,
            'metode_pembayaran' => 'cash',
            'status_pembayaran' => Transaksi::STATUS_LUNAS,
        ]);
        $this->assertDatabaseHas('kunjungans', [
            'id_kunjungan' => $transaksi->id_kunjungan,
            'status' => Kunjungan::STATUS_SELESAI,
        ]);
    }

    public function test_payment_is_rejected_when_cash_is_less_than_server_calculated_total(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $transaksi = $this->transaksiSiapBayar();

        $this->actingAs($staff)->put(route('transaksi.update', $transaksi), [
            'biaya_tindakan' => 50000,
            'biaya_obat' => 12500,
            'uang_dibayar' => 60000,
            'metode_pembayaran' => 'cash',
        ])->assertSessionHas('error')
            ->assertSessionHasInput('uang_dibayar', 60000)
            ->assertSessionHasInput('payment_transaction_id', $transaksi->id_transaksi);

        $this->assertDatabaseHas('transaksis', [
            'id_transaksi' => $transaksi->id_transaksi,
            'status_pembayaran' => Transaksi::STATUS_BELUM,
        ]);
        $this->assertDatabaseHas('kunjungans', [
            'id_kunjungan' => $transaksi->id_kunjungan,
            'status' => Kunjungan::STATUS_SIAP_BAYAR,
        ]);
    }

    public function test_payment_rejects_amounts_beyond_database_capacity_and_keeps_input(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $transaksi = $this->transaksiSiapBayar();

        $this->actingAs($staff)->put(route('transaksi.update', $transaksi), [
            'biaya_tindakan' => 10000000000,
            'biaya_obat' => 0,
            'uang_dibayar' => 10000000000,
            'metode_pembayaran' => 'cash',
        ])->assertSessionHasErrors(['biaya_tindakan', 'uang_dibayar'])
            ->assertSessionHasInput('biaya_tindakan', 10000000000)
            ->assertSessionHasInput('payment_transaction_id', $transaksi->id_transaksi);

        $this->assertSame(Transaksi::STATUS_BELUM, $transaksi->fresh()->status_pembayaran);
        $this->assertSame(Kunjungan::STATUS_SIAP_BAYAR, $transaksi->kunjungan->fresh()->status);
    }

    public function test_payment_rejects_combined_total_beyond_database_capacity(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $transaksi = $this->transaksiSiapBayar();

        $this->actingAs($staff)->put(route('transaksi.update', $transaksi), [
            'biaya_tindakan' => 6000000000,
            'biaya_obat' => 4000000000,
            'uang_dibayar' => 9999999999,
            'metode_pembayaran' => 'debit',
        ])->assertSessionHas('error')
            ->assertSessionHasInput('biaya_obat', 4000000000)
            ->assertSessionHasInput('metode_pembayaran', 'debit');

        $this->assertSame(Transaksi::STATUS_BELUM, $transaksi->fresh()->status_pembayaran);
    }

    private function transaksiSiapBayar(): Transaksi
    {
        $pasien = Pasien::create([
            'nama' => 'Budi Santoso',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'no_hp' => '081234567891',
        ]);
        $kunjungan = Kunjungan::create([
            'id_pasien' => $pasien->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_SIAP_BAYAR,
        ]);

        return Transaksi::create([
            'id_kunjungan' => $kunjungan->id_kunjungan,
            'total_biaya' => 0,
            'status_pembayaran' => Transaksi::STATUS_BELUM,
        ]);
    }
}
