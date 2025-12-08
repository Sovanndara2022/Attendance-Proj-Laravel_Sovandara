<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'Drtongsreng@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@1234'), // change after first login
            ]
        );
    }
}
