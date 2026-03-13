<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instructor = User::where('role', 'instructor')->first() ?? User::where('role', 'admin')->first();

        $courses = [
            [
                'title' => 'Introduction to Product',
                'description' => 'Overview of product fundamentals',
                'department' => 'Engineering',
                'start_date' => now()->subDays(60),
                'deadline' => now()->addDays(120),
                'status' => 'Active',
            ],
            [
                'title' => 'Workplace Safety',
                'description' => 'Safety practices and compliance',
                'department' => 'Operations',
                'start_date' => now()->subDays(10),
                'deadline' => now()->addDays(50),
                'status' => 'Active',
            ],
        ];

        foreach ($courses as $c) {
            Course::updateOrCreate(
                ['title' => $c['title']],
                array_merge($c, ['instructor_id' => $instructor ? $instructor->id : null])
            );
        }

        $this->command->info('Courses seeded.');
    }
}
