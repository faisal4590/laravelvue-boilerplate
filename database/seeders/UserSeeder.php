<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
        ]);

        // Create 2 Teachers
        User::factory()
            ->count(2)
            ->role(User::ROLE_TEACHER)
            ->create([
                'email' => fn ($attributes, $index) =>
                    "teacher{$index}@example.com",
            ]);

        // Create 2 Regular Users
        User::factory()
            ->count(2)
            ->role(User::ROLE_USER)
            ->create([
                'email' => fn ($attributes, $index) =>
                    "user{$index}@example.com",
            ]);
    }
}
