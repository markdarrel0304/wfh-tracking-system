<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $departments = ['IT Department', 'HR', 'Finance', 'Operations', 'Marketing'];

    foreach ($departments as $name) {
        \App\Models\Department::firstOrCreate(['name' => $name]);
    }
}   
}
