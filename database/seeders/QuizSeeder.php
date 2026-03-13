<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = Course::first();
        if (!$course) {
            $this->command->warn('No course found; skipping quiz seeding.');
            return;
        }

        // Attach a quiz to the first module of the first course (if exists)
        $module = Module::where('course_id', $course->id)->first();
        if (!$module) {
            $this->command->warn('No module found for course; skipping quiz seeding.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id, 'module_id' => $module->id, 'title' => 'Intro Quiz'],
            ['description' => 'Short quiz for the first module', 'pass_percentage' => 70]
        );

        $questions = [
            [
                'question_text' => 'What is the main goal of product development?',
                'options' => [
                    ['text' => 'To build any feature', 'is_correct' => false],
                    ['text' => 'To solve user problems', 'is_correct' => true],
                    ['text' => 'To increase internal complexity', 'is_correct' => false],
                ],
            ],
            [
                'question_text' => 'Which practice improves code quality?',
                'options' => [
                    ['text' => 'Avoid tests', 'is_correct' => false],
                    ['text' => 'Write automated tests', 'is_correct' => true],
                    ['text' => 'Merge without reviews', 'is_correct' => false],
                ],
            ],
        ];

        $order = 1;
        foreach ($questions as $q) {
            $question = QuizQuestion::updateOrCreate(
                ['quiz_id' => $quiz->id, 'question_text' => $q['question_text']],
                ['order' => $order]
            );

            $optOrder = 1;
            foreach ($q['options'] as $opt) {
                QuizOption::updateOrCreate(
                    ['question_id' => $question->id, 'option_text' => $opt['text']],
                    ['is_correct' => $opt['is_correct'], 'order' => $optOrder]
                );
                $optOrder++;
            }

            $order++;
        }

        $this->command->info('Quizzes, questions and options seeded.');
    }
}
