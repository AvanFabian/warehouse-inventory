<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@warehouse.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Manager User',
            'email' => 'manager@warehouse.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'manager',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Staff User',
            'email' => 'staff@warehouse.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'staff',
            'is_active' => true,
        ]);
    }
}
