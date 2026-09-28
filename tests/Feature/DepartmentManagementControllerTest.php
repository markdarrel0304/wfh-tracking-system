<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Notifications\DepartmentAssignmentChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DepartmentManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_a_department(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.departments.store'), $this->departmentPayload())
            ->assertRedirect()
            ->assertSessionHas('success', 'Department created successfully.');

        $department = Department::where('code', 'ACID')->firstOrFail();

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Appraisal and Credit Investigation Department',
            'location' => 'Head Office',
            'department_head' => 'Allan Belsa Flores',
            'department_head_count' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.departments.update', $department), [
                ...$this->departmentPayload(),
                'location' => 'Special Economic Zone',
                'is_active' => 0,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Department updated successfully.');

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'location' => 'Special Economic Zone',
            'is_active' => false,
        ]);
    }

    public function test_department_names_can_be_reused_for_different_units(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Department::create([
            'name' => 'Construction Management Department',
            'code' => 'CMD',
            'location' => 'Head Office',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.departments.store'), [
                ...$this->departmentPayload(),
                'name' => 'Construction Management Department',
                'code' => 'CMD-SEZ',
                'location' => 'Special Economic Zone',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('departments', 2);
    }

    public function test_non_admin_cannot_manage_departments(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)
            ->get(route('admin.departments'))
            ->assertForbidden();
    }

    public function test_admin_can_add_an_employee_to_a_department_and_move_them_from_another_department(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $previousDepartment = Department::create([
            'name' => 'Operations',
            'code' => 'OPS',
            'location' => 'Head Office',
            'is_active' => true,
        ]);
        $targetDepartment = Department::create([
            'name' => 'Information Technology',
            'code' => 'IT',
            'location' => 'Head Office',
            'is_active' => true,
        ]);
        $workSchedule = WorkSchedule::create([
            'name' => 'Regular',
            'days_json' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
        ]);
        $employeeUser = User::factory()->create(['department_id' => $previousDepartment->id]);
        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'employee_number' => 'EMP-0001',
            'first_name' => 'Jamie',
            'last_name' => 'Santos',
            'department_id' => $previousDepartment->id,
            'position' => 'Analyst',
            'work_schedule_id' => $workSchedule->id,
            'date_hired' => '2025-01-01',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.departments.employees.store', $targetDepartment), ['employee_id' => $employee->id])
            ->assertRedirect()
            ->assertSessionHas('success', 'Jamie Santos is now assigned to Information Technology.');

        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'department_id' => $targetDepartment->id]);
        $this->assertDatabaseHas('users', ['id' => $employeeUser->id, 'department_id' => $targetDepartment->id]);
        Notification::assertSentTo($employeeUser, DepartmentAssignmentChanged::class);
    }

    /** @return array<string, mixed> */
    private function departmentPayload(): array
    {
        return [
            'name' => 'Appraisal and Credit Investigation Department',
            'code' => 'acid',
            'location' => 'Head Office',
            'department_head' => 'Allan Belsa Flores',
            'department_head_count' => 2,
            'is_active' => 1,
        ];
    }
}
