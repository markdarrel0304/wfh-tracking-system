<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
{
    \App\Models\WorkSchedule::firstOrCreate(
        ['name' => 'Standard 9-6'],
        ['time_in' => '09:00', 'time_out' => '18:00']
    );

    \App\Models\WorkSchedule::firstOrCreate(
        ['name' => 'Flexible 8-5'],
        ['time_in' => '08:00', 'time_out' => '17:00']
    );
}
}
