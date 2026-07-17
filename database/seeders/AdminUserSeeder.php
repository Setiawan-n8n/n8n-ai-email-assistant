<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@sgu.local'],
            [
                'name' => 'Admin SGU',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
