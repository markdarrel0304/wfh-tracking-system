<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\View\Components\ScheduleDatePicker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_date_picker_does_not_link_to_the_work_schedule(): void
    {
        [$user] = $this->createEmployeeAccount();

        $this->actingAs($user)
            ->get(route('my-profile'))
            ->assertOk()
            ->assertDontSee('window.location.href');
    }

    public function test_date_picker_normalizes_a_datetime_value_to_a_date(): void
    {
        $datePicker = new ScheduleDatePicker('date_of_birth', '2005-06-02 00:00:00');
        $view = $datePicker->render();

        $this->assertSame('2005-06-02', $view->getData()['selectedDate']);
    }

    public function test_employee_can_update_their_profile_with_a_photo(): void
    {
        Storage::fake('public');
        [$user, $employee] = $this->createEmployeeAccount();
        $photo = UploadedFile::fake()->createWithContent(
            'profile.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9J4J4AAAAASUVORK5CYII=')
        );

        $this->actingAs($user)
            ->patch(route('my-profile.update'), [
                'first_name' => 'Mia',
                'last_name' => 'Santos',
                'photo' => $photo,
            ])
            ->assertRedirect(route('my-profile'))
            ->assertSessionHas('profile_success');

        $updatedEmployee = $employee->fresh();

        $this->assertSame('Mia', $updatedEmployee->first_name);
        $this->assertNotNull($updatedEmployee->photo);
        Storage::disk('public')->assertExists($updatedEmployee->photo);
    }

    public function test_employee_cannot_view_or_update_another_employees_profile(): void
    {
        [$user, $employee] = $this->createEmployeeAccount();
        $otherUser = User::factory()->create(['role' => 'employee', 'department_id' => $employee->department_id]);
        $otherEmployee = Employee::create([
            'user_id' => $otherUser->id,
            'employee_number' => 'EMP-0002',
            'first_name' => 'Noah',
            'last_name' => 'Reyes',
            'department_id' => $employee->department_id,
            'position' => 'Engineer',
            'date_hired' => '2025-01-01',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('employees.profile', $otherEmployee))
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('employees.profile', $otherEmployee), [
                'first_name' => 'Changed',
                'last_name' => 'Reyes',
            ])
            ->assertForbidden();

        $this->assertSame('Noah', $otherEmployee->fresh()->first_name);
    }

    public function test_admin_can_manage_an_employee_profile(): void
    {
        [, $employee] = $this->createEmployeeAccount();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('employees.profile', $employee), [
                'first_name' => 'Amelia',
                'last_name' => 'Santos',
            ])
            ->assertRedirect(route('employees.profile', $employee));

        $this->assertSame('Amelia', $employee->fresh()->first_name);
    }

    /** @return array{0: User, 1: Employee} */
    private function createEmployeeAccount(): array
    {
        $department = Department::create(['name' => 'Engineering']);
        $user = User::factory()->create(['role' => 'employee', 'department_id' => $department->id]);
        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-0001',
            'first_name' => 'Mia',
            'last_name' => 'Santos',
            'department_id' => $department->id,
            'position' => 'Engineer',
            'date_hired' => '2025-01-01',
            'status' => 'active',
        ]);

        return [$user, $employee];
    }
}
