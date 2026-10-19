<?php

namespace Tests\Feature;

use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffFeatureAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_open_all_staff_pages(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('dashboard'))->assertOk();
        $this->actingAs($staff)->get(route('pasien.index'))->assertOk();
        $this->actingAs($staff)->get(route('pasien.create'))->assertOk();
        $this->actingAs($staff)->get(route('kunjungan.index'))->assertOk();
        $this->actingAs($staff)->get(route('transaksi.index'))->assertOk();
        $this->actingAs($staff)->get(route('riwayat-transaksi.index'))->assertOk();
        $this->actingAs($staff)->get(route('dokter.index'))->assertOk();
    }

    public function test_doctor_cannot_open_staff_only_pages(): void
    {
        $doctor = User::factory()->create(['role' => 'dokter']);

        $this->actingAs($doctor)->get(route('pasien.index'))->assertForbidden();
        $this->actingAs($doctor)->get(route('pasien.create'))->assertForbidden();
        $this->actingAs($doctor)->get(route('transaksi.index'))->assertForbidden();
        $this->actingAs($doctor)->get(route('riwayat-transaksi.index'))->assertForbidden();
        $this->actingAs($doctor)->get(route('dokter.index'))->assertForbidden();
    }

    public function test_staff_can_update_patient_but_cannot_delete_patient_with_history(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $patient = Pasien::create($this->patientData());

        $this->actingAs($staff)->put(route('pasien.update', $patient->id_pasien), [
            ...$this->patientData(),
            'nama' => 'Nama Pasien Diperbarui',
        ])->assertRedirect(route('pasien.index'));

        $this->assertDatabaseHas('pasiens', [
            'id_pasien' => $patient->id_pasien,
            'nama' => 'Nama Pasien Diperbarui',
        ]);

        Kunjungan::create([
            'id_pasien' => $patient->id_pasien,
            'tgl_kunjungan' => now()->toDateString(),
            'poli_tujuan' => 'Poli Umum',
            'status' => Kunjungan::STATUS_SELESAI,
        ]);

        $this->actingAs($staff)->delete(route('pasien.destroy', $patient->id_pasien))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('pasiens', ['id_pasien' => $patient->id_pasien]);
    }

    /** @return array<string, string> */
    private function patientData(): array
    {
        return [
            'nama' => 'Pasien Audit',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Perempuan',
            'no_hp' => '081234567890',
            'nik' => '3273011201900001',
            'pekerjaan' => 'Karyawan',
            'alamat' => 'Jl. Audit No. 1',
        ];
    }
}
