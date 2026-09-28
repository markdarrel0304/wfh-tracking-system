<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use App\Notifications\AccountSettingsUpdated;
use App\Notifications\PasswordResetByAdministrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_manage_and_reset_a_user_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $department = Department::create(['name' => 'Engineering']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Mia Santos',
                'email' => 'mia@example.com',
                'department_id' => $department->id,
                'role' => 'supervisor',
                'password' => 'secure-password',
                'password_confirmation' => 'secure-password',
            ])
            ->assertRedirect();

        $managedUser = User::query()->where('email', 'mia@example.com')->firstOrFail();
        $this->assertTrue($managedUser->is_active);
        $this->assertTrue(Hash::check('secure-password', $managedUser->password));
        Notification::fake();

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $managedUser), [
                'name' => 'Mia Santos',
                'email' => 'mia@example.com',
                'department_id' => $department->id,
                'role' => 'employee',
                'is_active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $managedUser->id, 'role' => 'employee', 'is_active' => false]);
        Notification::assertSentTo($managedUser, AccountSettingsUpdated::class);

        $this->actingAs($admin)
            ->patch(route('admin.users.password', $managedUser), [
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('new-secure-password', $managedUser->fresh()->password));
        Notification::assertSentTo($managedUser, PasswordResetByAdministrator::class);
    }

    public function test_employee_cannot_access_user_management(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)
            ->get(route('admin.users'))
            ->assertForbidden();
    }

    public function test_last_active_administrator_cannot_be_deactivated(): void
    {
        $administrator = User::factory()->create(['role' => 'admin']);

        $this->actingAs($administrator)
            ->patch(route('admin.users.update', $administrator), [
                'name' => $administrator->name,
                'email' => $administrator->email,
                'department_id' => null,
                'role' => 'admin',
                'is_active' => false,
            ])
            ->assertSessionHasErrors('role');

        $this->assertTrue($administrator->fresh()->is_active);
    }
}
