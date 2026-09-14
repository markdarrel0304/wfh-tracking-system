<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_number' => 'EMP-' . fake()->unique()->numerify('####'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'department_id' => Department::inRandomOrder()->first()->id,
            'position' => fake()->jobTitle(),
            'work_schedule_id' => WorkSchedule::inRandomOrder()->first()->id,
            'date_hired' => fake()->dateTimeBetween('-3 years', 'now'),
            'status' => 'active',
        ];
    }
}