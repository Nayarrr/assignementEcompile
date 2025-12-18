<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'is_admin' => false,
        ]);
    }
}
