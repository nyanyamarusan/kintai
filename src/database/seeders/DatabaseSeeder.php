<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->count(5)->create();

        User::factory()->create([
            'name' => '田中 太郎',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $staffUsers = User::where('role', 'staff')->get();
        foreach ($staffUsers as $user) {
            Attendance::factory()->count(5)->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
