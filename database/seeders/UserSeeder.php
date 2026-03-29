<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@ifi.ph'],
            [
                'name' => 'Parish Admin',
                'password' => Hash::make('admin123'),
                'role' => 'Admin'
            ]
        );

        // Treasurer
        User::updateOrCreate(
            ['email' => 'treasurer@ifi.ph'],
            [
                'name' => 'Parish Treasurer',
                'password' => Hash::make('treasurer123'),
                'role' => 'Treasurer'
            ]
        );

        // Priest
        User::updateOrCreate(
            ['email' => 'priest@ifi.ph'],
            [
                'name' => 'Fr. Juana',
                'password' => Hash::make('priest123'),
                'role' => 'Priest'
            ]
        );
    }
}
