<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DoctorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the user already exists
        $email = 'doctor@example.com';
        if (!User::where('email', $email)->exists()) {
            User::factory()->create([
                'name' => 'Doctor Nada',
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'doctor',
                'email_verified_at' => now(), // ensure they are verified
            ]);
        }
    }
}