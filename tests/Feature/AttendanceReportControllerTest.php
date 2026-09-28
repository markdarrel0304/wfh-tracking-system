<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_an_employee_from_the_attendance_report_and_export_their_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $department = Department::create(['name' => 'Engineering']);
        $employee = $this->createEmployee($department, 'Mia', 'Lopez', 'EMP-1001');
        $otherEmployee = $this->createEmployee($department, 'Noah', 'Santos', 'EMP-1002');

        Attendance::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
            'status' => 'present',
        ]);
        Attendance::create([
            'employee_id' => $otherEmployee->id,
            'date' => '2026-09-21',
            'time_in' => '08:15:00',
            'time_out' => '17:00:00',
            'status' => 'late',
        ]);

        $query = ['from' => '2026-09-01', 'to' => '2026-09-30'];

        $this->actingAs($admin)
            ->get(route('reports.attendance', $query))
            ->assertOk()
            ->assertSee('Mia Lopez')
            ->assertSee('Noah Santos')
            ->assertSee('View attendance');

        $this->actingAs($admin)
            ->get(route('reports.attendance.employee', array_merge(['employee' => $employee], $query)))
            ->assertOk()
            ->assertSee('Mia Lopez')
            ->assertDontSee('Noah Santos')
            ->assertSee('8h 00m');

        $this->actingAs($admin)
            ->get(route('reports.attendance.export', array_merge(['employee_id' => $employee->id], $query)))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_employee_cannot_access_the_attendance_report(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)
            ->get(route('reports.attendance'))
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
