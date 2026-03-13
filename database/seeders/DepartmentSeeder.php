<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Human Resources', 'description' => 'HR and people operations'],
            ['name' => 'Engineering', 'description' => 'Product and engineering teams'],
            ['name' => 'Sales', 'description' => 'Sales and customer success'],
            ['name' => 'Operations', 'description' => 'Operations and admin'],
        ];

        // Try to assign a head if an instructor/admin exists
        $head = User::whereIn('role', ['admin', 'instructor'])->first();

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['name' => $dept['name']],
                [
                    'description' => $dept['description'],
                    'head_id' => $head ? $head->id : null,
                ]
            );
        }

        $this->command->info('Departments seeded.');
    }
}
