<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Section;

class StudentsSectionsSeeder extends Seeder
{
    public function run()
    {
        // Get the doctor user. Assumes DoctorUserSeeder ran first.
        $doctor = User::where('role', 'doctor')->first();
        
        // If no doctor, create one as a fallback.
        if (!$doctor) {
             $doctor = User::factory()->create([
                'name' => 'Default Doctor',
                'email' => 'doctor@example.com',
                'password' => Hash::make('password'),
                'role' => 'doctor',
            ]);
        }
        $creatorId = $doctor->id;

        // Ensure sections 1 and 2 exist (create if missing)
        $section1 = Section::firstOrCreate(
            ['name' => 'Section 1'], // <-- THE FIX (was 'title')
            [
                'description'=> 'Automatically created section 1 (seed).',
                'created_by' => $creatorId,
                'teacher_id' => $creatorId,
            ]
        );

        $section2 = Section::firstOrCreate(
            ['name' => 'Section 2'], // <-- THE FIX (was 'title')
            [
                'description'=> 'Automatically created section 2 (seed).',
                'created_by' => $creatorId,
                'teacher_id' => $creatorId
            ]
        );

        // Students to create (name, student_number)
        $students = [
            ['name' => 'Ahmad Al-Masri',   'student_number' => 'S2025001'],
            ['name' => 'Lina Saeed',       'student_number' => 'S2025002'],
            ['name' => 'Khaled Ibrahim',   'student_number' => 'S2025003'],
            ['name' => 'Mona Hamed',       'student_number' => 'S2025004'],
            ['name' => 'Omar Haddad',      'student_number' => 'S2025005'],
            ['name' => 'Sara Youssef',     'student_number' => 'S2025006'],
            ['name' => 'Tamer Alnouri',    'student_number' => 'S2025007'],
            ['name' => 'Rana Khalil',      'student_number' => 'S2025008'],
        ];

        // Create students and attach them
        foreach ($students as $idx => $s) {
            $email = Str::slug($s['name'], '.') . '@example.test'; 
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $s['name'],
                    'student_number' => $s['student_number'] ?? ('SN' . mt_rand(1000,9999)),
                    'password' => Hash::make('password'),
                    'role' => 'student' // Added role
                ]
            );

            // attach to section 1 or 2
            // attach to section 1 or 2
            // $joinedAt = Carbon::now(); // <-- We don't need this
            if ($idx < 4) {
                $section1->users()->syncWithoutDetaching([
                    $user->id 
                ]);
            } else {
                $section2->users()->syncWithoutDetaching([
                    $user->id
                ]);
            }
            $this->command->info("Student created/linked: {$user->email}");
        }
        $this->command->info('Students and sections seeding completed.');
    }
}