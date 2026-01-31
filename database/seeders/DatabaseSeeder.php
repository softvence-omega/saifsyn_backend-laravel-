<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $this->call([
            RolesTableSeeder::class,
            TermsAndConditionSeeder::class,
        ]);

        // Create users
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
                'status' => true,
            ]
        );
        $admin->assignRole('admin');

        

        $user = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
                'status' => true,
            ]
        );
        $user->assignRole('user');

       
    }
}
