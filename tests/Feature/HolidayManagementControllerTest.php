<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\User;
use App\Notifications\HolidayScheduleUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class HolidayManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_a_holiday(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.holidays.store'), $this->holidayPayload())
            ->assertRedirect()
            ->assertSessionHas('success', 'Holiday added successfully.');

        $holiday = Holiday::where('name', 'Foundation Day')->firstOrFail();

        $this->assertDatabaseHas('holidays', [
            'id' => $holiday->id,
            'date' => '2026-10-15 00:00:00',
            'type' => 'special',
            'is_recurring' => 1,
            'is_active' => 1,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.holidays.update', $holiday), [...$this->holidayPayload(), 'is_active' => 0])
            ->assertRedirect()
            ->assertSessionHas('success', 'Holiday updated successfully.');

        $this->assertDatabaseHas('holidays', ['id' => $holiday->id, 'is_active' => false]);
    }

    public function test_non_admin_cannot_manage_holidays(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)
            ->get(route('admin.holidays'))
            ->assertForbidden();
    }

    public function test_creating_a_holiday_notifies_active_employees(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $department = Department::create(['name' => 'Engineering']);
        Employee::create([
            'user_id' => $employeeUser->id,
            'employee_number' => 'EMP-0001',
            'first_name' => 'Mia',
            'last_name' => 'Santos',
            'department_id' => $department->id,
            'position' => 'Engineer',
            'date_hired' => '2025-01-01',
            'status' => 'active',
        ]);
        Notification::fake();

        $this->actingAs($admin)
            ->post(route('admin.holidays.store'), $this->holidayPayload())
            ->assertRedirect();

        Notification::assertSentTo($employeeUser, HolidayScheduleUpdated::class);
    }

    /** @return array<string, mixed> */
    private function holidayPayload(): array
    {
        return [
            'name' => 'Foundation Day',
            'date' => '2026-10-15',
            'type' => 'special',
            'is_recurring' => 1,
            'is_active' => 1,
        ];
    }
}
