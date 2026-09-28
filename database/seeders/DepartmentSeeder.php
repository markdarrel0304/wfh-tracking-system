<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Administrative and General Services Department', 'code' => 'AGSD', 'location' => 'Head Office'],
            ['name' => 'Finance Department', 'code' => 'FINANCE', 'location' => 'Head Office'],
            ['name' => 'Appraisal and Credit Investigation Department', 'code' => 'ACID', 'location' => 'Head Office'],
            ['name' => 'Construction Management Department', 'code' => 'CMD', 'location' => 'Head Office'],
            ['name' => 'Office of the President', 'code' => 'OPCEO', 'location' => 'Head Office'],
            ['name' => 'Property Management and Maintenance Services Department', 'code' => 'PMMS', 'location' => 'Head Office'],
            ['name' => 'Economic Zone Management Department', 'code' => 'EZMD', 'location' => 'Special Economic Zone'],
        ];

        foreach ($departments as $department) {
            Department::query()->updateOrCreate(
                ['code' => $department['code']],
                [...$department, 'is_active' => true],
            );
        }
    }
}
