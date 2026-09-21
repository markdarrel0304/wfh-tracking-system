<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\WfhRequest;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WfhApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_a_pending_wfh_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $approver = $this->createEmployeeFor($admin);
        $employee = $this->createEmployeeFor(User::factory()->create());
        $wfhRequest = $this->createPendingRequestFor($employee);

        $response = $this->actingAs($admin)->patch(route('wfh.requests.approval', $wfhRequest), [
            'status' => 'approved',
            'remarks' => 'Approved for the requested work days.',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('wfh.approval'));

        $this->assertDatabaseHas('wfh_requests', [
            'id' => $wfhRequest->id,
            'status' => 'approved',
            'approver_id' => $approver->id,
            'remarks' => 'Approved for the requested work days.',
        ]);
        $this->assertNotNull($wfhRequest->fresh()->approved_at);
    }

    public function test_admin_can_reject_a_pending_wfh_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $approver = $this->createEmployeeFor($admin);
        $employee = $this->createEmployeeFor(User::factory()->create());
        $wfhRequest = $this->createPendingRequestFor($employee);

        $this->actingAs($admin)
            ->patch(route('wfh.requests.approval', $wfhRequest), [
                'status' => 'rejected',
                'remarks' => 'Please submit a request with dates two working days in advance.',
            ])
            ->assertRedirect(route('wfh.approval'));

        $this->assertDatabaseHas('wfh_requests', [
            'id' => $wfhRequest->id,
            'status' => 'rejected',
            'approver_id' => $approver->id,
            'remarks' => 'Please submit a request with dates two working days in advance.',
            'approved_at' => null,
        ]);
    }

    public function test_regular_employee_cannot_access_wfh_approval_queue(): void
    {
        $employeeUser = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employeeUser)
            ->get(route('wfh.approval'))
            ->assertForbidden();
    }

    public function test_supervisor_can_access_the_wfh_approval_queue(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);

        $this->actingAs($supervisor)
            ->get(route('wfh.approval'))
            ->assertOk();
    }

    private function createEmployeeFor(User $user): Employee
    {
        $department = Department::firstOrCreate(['name' => 'Information Technology']);
        $workSchedule = WorkSchedule::firstOrCreate(
            ['name' => 'Regular'],
            ['days_json' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']), 'time_in' => '08:00:00', 'time_out' => '17:00:00'],
        );

        return Employee::create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'department_id' => $department->id,
            'position' => 'Employee',
            'work_schedule_id' => $workSchedule->id,
            'date_hired' => '2025-01-01',
            'status' => 'active',
        ]);
    }

    private function createPendingRequestFor(Employee $employee): WfhRequest
    {
        return WfhRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'Work From Home',
            'date_from' => '2026-09-28',
            'date_to' => '2026-09-28',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'reason' => 'Focused work from home.',
            'status' => 'pending',
        ]);
    }
}
