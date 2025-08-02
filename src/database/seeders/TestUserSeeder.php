<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => '山田太郎',
            'email' => 'user@example.com',
            'password' => Hash::make('userpassword'),
            'email_verified_at' => now(),
        ]);

        Admin::create([
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword'),
        ]);
    }
}
