<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_role_permissions_and_assignments(): void
    {
        $admin = User::factory()->create(['name' => 'Ada Admin', 'role' => 'admin']);
        User::factory()->create(['name' => 'Sam Supervisor', 'role' => 'supervisor']);
        User::factory()->create(['name' => 'Eli Employee', 'role' => 'employee', 'is_active' => false]);

        $this->actingAs($admin)
            ->get(route('admin.roles'))
            ->assertOk()
            ->assertSee('Roles and access')
            ->assertSee('Submit and track personal WFH requests')
            ->assertSee('Review WFH requests')
            ->assertSee('Manage users, roles, departments, schedules, and holidays')
            ->assertSee('Ada Admin')
            ->assertSee('Sam Supervisor')
            ->assertSee('Eli Employee');
    }

    public function test_employee_cannot_view_roles_management(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)
            ->get(route('admin.roles'))
            ->assertForbidden();
    }
}
