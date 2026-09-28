<?php

namespace Tests\Feature;

use App\Models\AccomplishmentReport;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccomplishmentReportSummaryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_an_employee_accomplishment_history_and_export_it(): void
    {
        $department = Department::create(['name' => 'Operations']);
        $administrator = User::factory()->create(['role' => 'admin']);
        $employee = $this->createEmployee($department, 'Mia', 'Santos');
        $otherEmployee = $this->createEmployee($department, 'Noah', 'Reyes');

        AccomplishmentReport::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'summary' => 'Completed the authentication flow.',
            'next_steps' => 'Prepare release notes.',
            'status' => 'reviewed',
            'submitted_at' => '2026-09-21 17:10:00',
            'reviewed_at' => '2026-09-22 08:00:00',
        ]);
        AccomplishmentReport::create([
            'employee_id' => $otherEmployee->id,
            'date' => '2026-09-21',
            'summary' => 'Completed an unrelated report.',
            'status' => 'submitted',
            'submitted_at' => '2026-09-21 17:10:00',
        ]);

        $filters = ['from' => '2026-09-01', 'to' => '2026-09-30'];

        $this->actingAs($administrator)
            ->get(route('reports.accomplishments', $filters))
            ->assertOk()
            ->assertSee('Mia Santos')
            ->assertSee('Noah Reyes');

        $this->actingAs($administrator)
            ->get(route('reports.accomplishments.employee', array_merge(['employee' => $employee], $filters)))
            ->assertOk()
            ->assertSee('Completed the authentication flow.')
            ->assertDontSee('Completed an unrelated report.');

        $this->actingAs($administrator)
            ->get(route('reports.accomplishments.export', array_merge(['employee_id' => $employee->id], $filters)))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_employee_cannot_access_accomplishment_reports(): void
    {
        $employeeUser = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employeeUser)
            ->get(route('reports.accomplishments'))
            ->assertForbidden();
    }

    private function createEmployee(Department $department, string $firstName, string $lastName): Employee
    {
        return Employee::create([
            'user_id' => User::factory()->create(['role' => 'employee'])->id,
            'employee_number' => strtoupper($firstName).'-'.strtoupper($lastName),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'department_id' => $department->id,
            'position' => 'Staff member',
            'date_hired' => '2026-01-01',
            'status' => 'active',
        ]);
    }
}
