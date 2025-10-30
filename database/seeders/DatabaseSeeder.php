<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // We will not run the broken factory:
        // User::factory(10)->create();

        // Instead, we will call your two important seeders directly:
        $this->call([
            DoctorUserSeeder::class,
            StudentsSectionsSeeder::class,
        ]);
    }
}