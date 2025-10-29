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
        // تأكّد من أن هناك مستخدم (doctor/admin) لنعطي created_by للأقسام إن لزم
        $creatorId = 1; // غيّر إذا تريد ID آخر

        // Ensure sections 1 and 2 exist (create if missing)
        $section1 = Section::find(1);
        if (!$section1) {
            $section1 = Section::create([
                'title'      => 'Section 1',
                'description'=> 'Automatically created section 1 (seed).',
                'created_by' => $creatorId,
            ]);
        }

        $section2 = Section::find(2);
        if (!$section2) {
            $section2 = Section::create([
                'title'      => 'Section 2',
                'description'=> 'Automatically created section 2 (seed).',
                'created_by' => $creatorId,
            ]);
        }

        // Students to create (name, student_number)
        $students = [
            ['name' => 'Ahmad Al-Masri',   'student_number' => 'S2025001'],
            ['name' => 'Lina Saeed',      'student_number' => 'S2025002'],
            ['name' => 'Khaled Ibrahim',  'student_number' => 'S2025003'],
            ['name' => 'Mona Hamed',      'student_number' => 'S2025004'],
            ['name' => 'Omar Haddad',     'student_number' => 'S2025005'],
            ['name' => 'Sara Youssef',    'student_number' => 'S2025006'],
            ['name' => 'Tamer Alnouri',   'student_number' => 'S2025007'],
            ['name' => 'Rana Khalil',     'student_number' => 'S2025008'],
        ];

        // Create students and attach them: first 4 -> section1, last 4 -> section2
        foreach ($students as $idx => $s) {
            $email = Str::slug($s['name'], '.') . '@example.test'; // e.g. ahmad-al-masri@example.test
            // Avoid duplicates: إذا الايميل موجود ما نعيد الإنشاء
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $s['name'],
                    'student_number' => $s['student_number'] ?? ('SN' . mt_rand(1000,9999)),
                    'password' => Hash::make('password'), // كلمة افتراضية: password — غيّرها بعدين
                    // لو عندك حقل role أو type اضف هنا 'role' => 'student'
                ]
            );

            // attach to section 1 or 2
            $joinedAt = Carbon::now();
            if ($idx < 4) {
                // section 1
                // استخدم syncWithoutDetaching مع بيانات pivot ليمنع تكرار
                $section1->users()->syncWithoutDetaching([
                    $user->id => ['joined_at' => $joinedAt]
                ]);
            } else {
                // section 2
                $section2->users()->syncWithoutDetaching([
                    $user->id => ['joined_at' => $joinedAt]
                ]);
            }

            $this->command->info("Student created/linked: {$user->email} -> section " . ($idx < 4 ? '1' : '2'));
        }

        $this->command->info('Students and sections seeding completed.');
    }
}
