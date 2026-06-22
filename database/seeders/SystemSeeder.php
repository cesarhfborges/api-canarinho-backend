<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SystemSeeder extends Seeder
{
    public function run()
    {
        // Minimum setup for production
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrador',
                'email' => 'admin@canarinho.local',
                'password' => Hash::make('canarinho1234'), // Password must be changed after deployment
                'is_admin' => true,
                'is_active' => true
            ]
        );
    }
}
