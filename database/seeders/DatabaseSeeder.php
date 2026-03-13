<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@maptech.com'],
            [
                'fullname' => 'Admin User',
                'password' => 'password123',
                'role' => 'admin',
                'status' => 'Active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'instructor@maptech.com'],
            [
                'fullname' => 'Instructor User',
                'password' => 'password123',
                'role' => 'instructor',
                'status' => 'Active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'employee@maptech.com'],
            [
                'fullname' => 'Employee User',
                'password' => 'password123',
                'role' => 'employee',
                'status' => 'Active',
            ]
        );

        // Call other seeders to build a complete example dataset
        $this->call([
            DepartmentSeeder::class,
            SubdepartmentSeeder::class,
            CourseSeeder::class,
            ModuleLessonSeeder::class,
            QuizSeeder::class,
            CourseEnrollmentSeeder::class,
        ]);
    }
}
