<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class ModuleLessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            for ($m = 1; $m <= 2; $m++) {
                $module = Module::updateOrCreate(
                    ['course_id' => $course->id, 'title' => "Module {$m} - {$course->title}"],
                    ['description' => "Module {$m} for {$course->title}", 'order' => $m]
                );

                for ($l = 1; $l <= 2; $l++) {
                    Lesson::updateOrCreate(
                        ['module_id' => $module->id, 'title' => "Lesson {$l} - {$module->title}"],
                        ['text_content' => "Content for lesson {$l} of module {$m} in course {$course->title}", 'order' => $l]
                    );
                }
            }
        }

        $this->command->info('Modules and lessons seeded.');
    }
}
