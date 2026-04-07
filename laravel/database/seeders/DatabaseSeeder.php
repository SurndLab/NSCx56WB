<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'publisher_id' => null,
                'display_name' => 'Super Admin',
                'role' => User::ROLE_SUPER_ADMIN,
                'password' => Hash::make('1234'),
                'is_active' => true,
            ]
        );
    }
}
