<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@gourmethaven.test'],
            [
                'name' => 'Gourmet Haven Admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
