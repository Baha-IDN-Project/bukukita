<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Membuat Admin (Kode Lama Anda)
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'email_verified_at' => now()
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Ahmad Bahaudin Mudhary',
                'password' => Hash::make('12345678'),
                'role' => 'user',
                'email_verified_at' => now()
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Ahmad Bahaudin Mudhary',
                'password' => Hash::make('12345678'),
                'role' => 'user',
                'email_verified_at' => now()
            ]
        );

        User::firstOrCreate(
            ['email' => 'mahiru@gmail.com'],
            [
                'name' => 'Shiina Mahiru',
                'password' => Hash::make('12345678'),
                'role' => 'user',
                'email_verified_at' => now()
            ]
        );

        User::firstOrCreate(
            ['email' => 'illya@gmail.com'],
            [
                'name' => 'Illyasviel Von Einzbern',
                'password' => Hash::make('12345678'),
                'role' => 'user',
                'email_verified_at' => now()
            ]
        );
    }
}
