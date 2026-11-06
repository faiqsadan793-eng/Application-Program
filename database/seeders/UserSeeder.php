<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Password dapat dikonfigurasi melalui .env (SEEDER_DOKTER_PASSWORD / SEEDER_STAFF_PASSWORD)
        // Jika tidak diset, gunakan default untuk kemudahan development lokal.
        $dokterPassword = env('SEEDER_DOKTER_PASSWORD', 'password123');
        $staffPassword  = env('SEEDER_STAFF_PASSWORD', 'password123');

        // Akun Dokter
        User::firstOrCreate(
            ['email' => 'dokter@klinik.com'],
            [
                'name'               => 'dr. Budi Santoso',
                'password'           => Hash::make($dokterPassword),
                'role'               => 'dokter',
                'email_verified_at'  => now(),
            ]
        );

        // Akun Staff / Kasir
        User::firstOrCreate(
            ['email' => 'staff@klinik.com'],
            [
                'name'               => 'Siti Rahma (Staff)',
                'password'           => Hash::make($staffPassword),
                'role'               => 'staff',
                'email_verified_at'  => now(),
            ]
        );
    }
}