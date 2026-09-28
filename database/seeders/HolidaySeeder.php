<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define recurring holidays (same date every year)
        $recurringHolidays = [
            ['name' => 'New Year\'s Day', 'month' => 1, 'day' => 1, 'type' => 'regular'],
            ['name' => 'Labor Day', 'month' => 5, 'day' => 1, 'type' => 'regular'],
            ['name' => 'Independence Day', 'month' => 6, 'day' => 12, 'type' => 'regular'],
            ['name' => 'National Heroes Day', 'month' => 8, 'day' => 30, 'type' => 'regular'],
            ['name' => 'Bonifacio Day', 'month' => 11, 'day' => 30, 'type' => 'regular'],
            ['name' => 'Christmas Day', 'month' => 12, 'day' => 25, 'type' => 'regular'],
            ['name' => 'Rizal Day', 'month' => 12, 'day' => 30, 'type' => 'regular'],
        ];

        // Add holidays for multiple years (2026-2030)
        $years = range(2026, 2030);

        foreach ($years as $year) {
            foreach ($recurringHolidays as $holiday) {
                Holiday::create([
                    'name' => $holiday['name'],
                    'date' => sprintf('%04d-%02d-%02d', $year, $holiday['month'], $holiday['day']),
                    'is_recurring' => true,
                    'type' => $holiday['type'],
                ]);
            }
        }

        // Add movable holidays (Araw ng Kagitingan - varies by year)
        // For simplicity, we'll add them as fixed dates for now
        $movableHolidays = [
            ['name' => 'Araw ng Kagitingan', 'date' => '2026-04-09', 'type' => 'regular'],
            ['name' => 'Araw ng Kagitingan', 'date' => '2027-04-09', 'type' => 'regular'],
            ['name' => 'Araw ng Kagitingan', 'date' => '2028-04-09', 'type' => 'regular'],
            ['name' => 'Araw ng Kagitingan', 'date' => '2029-04-09', 'type' => 'regular'],
            ['name' => 'Araw ng Kagitingan', 'date' => '2030-04-09', 'type' => 'regular'],
        ];

        foreach ($movableHolidays as $holiday) {
            Holiday::create([
                'name' => $holiday['name'],
                'date' => $holiday['date'],
                'is_recurring' => false,
                'type' => $holiday['type'],
            ]);
        }
    }
}
