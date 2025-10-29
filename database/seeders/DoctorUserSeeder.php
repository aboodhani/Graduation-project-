<?php
// database/seeders/DoctorUserSeeder.php
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DoctorUserSeeder extends Seeder {
    public function run(): void {
        User::updateOrCreate(
            ['email' => 'doctor@example.com'],
            [
                'name' => 'Dr. John Doe',
                'password' => Hash::make('password'),
                'role' => 'doctor',
            ]
        );
    }
}
