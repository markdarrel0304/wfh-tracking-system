<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\WfhRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkScheduleManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_schedule_uses_an_approved_wfh_request_as_its_source_of_truth(): void
    {
        $this->travelTo('2026-09-28 09:00:00');
        $employee = $this->createEmployee();
        WfhRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'Work From Home',
            'date_from' => '2026-09-28',
            'date_to' => '2026-09-30',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'reason' => 'Focused remote work.',
            'status' => 'approved',
        ]);

        $this->actingAs($employee->user)
            ->get(route('my-work-schedule', ['year' => 2026, 'month' => 9, 'date' => '2026-09-28']))
            ->assertOk()
            ->assertSee('Approved WFH workday')
            ->assertSee('9:00 AM – 6:00 PM')
            ->assertSee('Approved WFH request');
    }

    public function test_pending_wfh_request_does_not_create_an_employee_schedule(): void
    {
        $this->travelTo('2026-09-28 09:00:00');
        $employee = $this->createEmployee();
        WfhRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'Work From Home',
            'date_from' => '2026-09-28',
            'date_to' => '2026-09-28',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'reason' => 'Pending review.',
            'status' => 'pending',
        ]);

        $this->actingAs($employee->user)
            ->get(route('my-work-schedule'))
            ->assertOk()
            ->assertSee('No approved WFH schedule today')
            ->assertDontSee('Approved WFH workday');
    }

    public function test_employee_can_only_time_in_for_an_approved_wfh_request(): void
    {
        $this->travelTo('2026-09-28 09:00:00');
        $employee = $this->createEmployee();

        $this->actingAs($employee->user)
            ->post(route('attendance.time-in'))
            ->assertSessionHas('attendance_error', 'You can time in after an administrator approves your WFH request.');

        WfhRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'Work From Home',
            'date_from' => '2026-09-28',
            'date_to' => '2026-09-28',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'reason' => 'Approved remote work.',
            'status' => 'approved',
        ]);

        $this->post(route('attendance.time-in'))
            ->assertRedirect(route('attendance.time-in-out'));

        $this->assertDatabaseHas('attendance', [
            'employee_id' => $employee->id,
            'date' => '2026-09-28 00:00:00',
            'wfh_request_id' => $employee->approvedWfhRequestFor('2026-09-28')->id,
        ]);
    }

    private function createEmployee(): Employee
    {
        $user = User::factory()->create(['role' => 'employee']);
        $department = Department::create(['name' => 'Information Technology']);

        return Employee::create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-1001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'department_id' => $department->id,
            'position' => 'Developer',
            'date_hired' => '2025-01-01',
            'status' => 'active',
        ]);
    }
}
