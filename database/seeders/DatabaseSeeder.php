<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin / Dosen
        User::updateOrCreate(
            ['username' => 'sokidbae'],
            [
                'name' => 'Sokid, M.Kom',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'kelas_id' => null,
            ]
        );

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Sokid, M.Kom',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'kelas_id' => null,
            ]
        );

        User::updateOrCreate(
            ['username' => '198910052025051003'],
            [
                'name' => 'Sokid, M.Kom',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'kelas_id' => null,
            ]
        );
    }
}
