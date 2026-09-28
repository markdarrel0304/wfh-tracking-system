<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\WfhRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WfhReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_an_employee_wfh_history_and_export_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $department = Department::create(['name' => 'Engineering']);
        $employee = $this->createEmployee($department, 'Mia', 'Lopez', 'EMP-1001');
        $otherEmployee = $this->createEmployee($department, 'Noah', 'Santos', 'EMP-1002');

        WfhRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'Work From Home',
            'date_from' => '2026-09-21',
            'date_to' => '2026-09-23',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'reason' => 'Home internet installation.',
            'status' => 'approved',
        ]);
        WfhRequest::create([
            'employee_id' => $otherEmployee->id,
            'request_type' => 'Work From Home',
            'date_from' => '2026-09-21',
            'date_to' => '2026-09-21',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'reason' => 'Focus work.',
            'status' => 'pending',
        ]);

        $query = ['from' => '2026-09-01', 'to' => '2026-09-30'];

        $this->actingAs($admin)
            ->get(route('reports.wfh', $query))
            ->assertOk()
            ->assertSee('Mia Lopez')
            ->assertSee('Noah Santos')
            ->assertSee('View WFH history');

        $this->actingAs($admin)
            ->get(route('reports.wfh.employee', array_merge(['employee' => $employee], $query)))
            ->assertOk()
            ->assertSee('Home internet installation.')
            ->assertDontSee('Focus work.')
            ->assertSee('3');

        $this->actingAs($admin)
            ->get(route('reports.wfh.export', array_merge(['employee_id' => $employee->id], $query)))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_employee_cannot_access_the_wfh_report(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)
            ->get(route('reports.wfh'))
            ->assertForbidden();
    }

    private function createEmployee(Department $department, string $firstName, string $lastName, string $employeeNumber): Employee
    {
        return Employee::create([
            'user_id' => User::factory()->create(['role' => 'employee'])->id,
            'employee_number' => $employeeNumber,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'department_id' => $department->id,
            'position' => 'Staff',
            'date_hired' => '2025-01-01',
            'status' => 'active',
        ]);
    }
}
