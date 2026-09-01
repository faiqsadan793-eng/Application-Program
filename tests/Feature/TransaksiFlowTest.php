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
        ])->assertSessionHas('error');

        $this->assertDatabaseHas('transaksis', [
            'id_transaksi' => $transaksi->id_transaksi,
            'status_pembayaran' => Transaksi::STATUS_BELUM,
        ]);
        $this->assertDatabaseHas('kunjungans', [
            'id_kunjungan' => $transaksi->id_kunjungan,
            'status' => Kunjungan::STATUS_SIAP_BAYAR,
        ]);
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
