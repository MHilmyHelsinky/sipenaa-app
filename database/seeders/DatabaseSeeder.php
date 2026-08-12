<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Membuat akun Super Admin pertama jika belum ada.
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Super Admin SIPENA',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
    }
}