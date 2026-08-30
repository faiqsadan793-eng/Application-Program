<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DokterManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_the_doctor_management_page(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)->get(route('dokter.index'));

        $response->assertOk();
        $response->assertSee('Manajemen Data Dokter');
    }

    public function test_staff_can_create_a_doctor_account_and_profile(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)->post(route('dokter.store'), [
            'name' => 'dr. Andi Saputra',
            'email' => 'andi@klinik.test',
            'password' => 'password123',
            'nip_sip' => 'SIP-001',
            'poli' => 'Poli Umum',
        ]);

        $response->assertRedirect(route('dokter.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'dr. Andi Saputra',
            'email' => 'andi@klinik.test',
            'role' => 'dokter',
        ]);
        $this->assertDatabaseHas('dokters', [
            'nip_sip' => 'SIP-001',
            'poli' => 'Poli Umum',
        ]);
    }

    public function test_staff_can_update_doctor_login_and_profile_data(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $user = User::factory()->create([
            'name' => 'dr. Lama',
            'email' => 'lama@klinik.test',
            'role' => 'dokter',
        ]);
        $dokter = Dokter::create([
            'user_id' => $user->id,
            'nip_sip' => 'SIP-002',
            'poli' => 'Poli Umum',
        ]);

        $response = $this->actingAs($staff)->put(route('dokter.update', $dokter), [
            'name' => 'dr. Baru',
            'email' => 'baru@klinik.test',
            'password' => 'passwordbaru',
            'nip_sip' => 'SIP-003',
            'poli' => 'Poli Gigi',
        ]);

        $response->assertRedirect(route('dokter.index'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'dr. Baru', 'email' => 'baru@klinik.test']);
        $this->assertDatabaseHas('dokters', ['id_dokter' => $dokter->id_dokter, 'nip_sip' => 'SIP-003', 'poli' => 'Poli Gigi']);
        $this->assertTrue($user->fresh()->password === null || password_verify('passwordbaru', $user->fresh()->password));
    }

    public function test_staff_can_delete_doctor_profile_and_login_account(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $user = User::factory()->create(['role' => 'dokter']);
        $dokter = Dokter::create([
            'user_id' => $user->id,
            'nip_sip' => 'SIP-004',
            'poli' => 'Poli Anak',
        ]);

        $response = $this->actingAs($staff)->delete(route('dokter.destroy', $dokter));

        $response->assertRedirect(route('dokter.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('dokters', ['id_dokter' => $dokter->id_dokter]);
    }

    public function test_doctor_cannot_manage_doctor_data(): void
    {
        $dokter = User::factory()->create(['role' => 'dokter']);

        $response = $this->actingAs($dokter)->post(route('dokter.store'), [
            'name' => 'dr. Tidak Diizinkan',
            'email' => 'tidak-diizinkan@klinik.test',
            'password' => 'password123',
            'nip_sip' => 'SIP-005',
            'poli' => 'Poli Umum',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'tidak-diizinkan@klinik.test']);
    }
}
