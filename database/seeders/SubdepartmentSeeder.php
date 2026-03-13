<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Subdepartment;
use Illuminate\Database\Seeder;

class SubdepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mapping = [
            'Human Resources' => ['Recruitment', 'Employee Relations'],
            'Engineering' => ['Backend', 'Frontend', 'QA'],
            'Sales' => ['Enterprise', 'SMB'],
            'Operations' => ['Facilities', 'Admin'],
        ];

        foreach ($mapping as $deptName => $subs) {
            $department = Department::where('name', $deptName)->first();
            if (!$department) continue;

            foreach ($subs as $name) {
                Subdepartment::updateOrCreate(
                    ['name' => $name, 'department_id' => $department->id],
                    ['description' => "{$name} of {$deptName}"]
                );
            }
        }

        $this->command->info('Subdepartments seeded.');
    }
}
