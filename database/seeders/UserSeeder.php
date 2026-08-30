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
        // Akun Dokter
        User::firstOrCreate(
            ['email' => 'dokter@klinik.com'],
            [
                'name'               => 'dr. Budi Santoso',
                'password'           => Hash::make('password123'),
                'role'               => 'dokter',
                'email_verified_at'  => now(),
            ]
        );

        // Akun Staff / Kasir
        User::firstOrCreate(
            ['email' => 'staff@klinik.com'],
            [
                'name'               => 'Siti Rahma (Staff)',
                'password'           => Hash::make('password123'),
                'role'               => 'staff',
                'email_verified_at'  => now(),
            ]
        );
    }
}