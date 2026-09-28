<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\WfhRequest;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_clock_in_and_a_late_status_is_recorded(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $this->travelTo('2026-09-21 08:05:00');
        WfhRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'Work From Home',
            'date_from' => '2026-09-21',
            'date_to' => '2026-09-21',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'reason' => 'Scheduled remote work.',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->post(route('attendance.time-in'))
            ->assertRedirect(route('attendance.time-in-out'))
            ->assertSessionHas('attendance_success', 'You are clocked in for today.');

        $this->assertDatabaseHas('attendance', [
            'employee_id' => $employee->id,
            'date' => '2026-09-21 00:00:00',
            'time_in' => '08:05:00',
            'status' => 'late',
        ]);
    }

    public function test_employee_cannot_clock_in_twice_on_the_same_day(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        Attendance::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'time_in' => '08:00:00',
            'status' => 'present',
        ]);
        $this->travelTo('2026-09-21 08:15:00');

        $this->actingAs($user)
            ->post(route('attendance.time-in'))
            ->assertRedirect()
            ->assertSessionHas('attendance_error', 'You have already clocked in today.');

        $this->assertSame(1, Attendance::where('employee_id', $employee->id)->count());
    }

    public function test_employee_can_clock_out_without_recording_lunch(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'time_in' => '08:00:00',
            'status' => 'present',
        ]);
        $this->travelTo('2026-09-21 17:15:00');

        $this->actingAs($user)
            ->patch(route('attendance.time-out'))
            ->assertRedirect(route('attendance.time-in-out'))
            ->assertSessionHas('attendance_success', 'Your time out was recorded.');

        $this->assertDatabaseHas('attendance', [
            'id' => $attendance->id,
            'time_out' => '17:15:00',
        ]);
    }

    public function test_employee_can_clock_out_early_at_any_time_after_clocking_in(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'time_in' => '08:05:00',
            'status' => 'late',
        ]);
        $this->travelTo('2026-09-21 09:20:00');

        $this->actingAs($user)
            ->patch(route('attendance.time-out-early'))
            ->assertRedirect(route('attendance.time-in-out'))
            ->assertSessionHas('attendance_success', 'Your early time out was recorded. Overtime is unavailable for this workday.');

        $this->assertDatabaseHas('attendance', [
            'id' => $attendance->id,
            'time_out' => '09:20:00',
            'early_out' => true,
        ]);
    }

    public function test_employee_cannot_clock_out_before_clocking_in(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $this->createEmployeeFor($user);
        $this->travelTo('2026-09-21 17:15:00');

        $this->actingAs($user)
            ->patch(route('attendance.time-out'))
            ->assertRedirect()
            ->assertSessionHas('attendance_error', 'Clock in before recording your time out.');
    }

    public function test_regular_work_time_automatically_excludes_the_fixed_lunch_hour(): void
    {
        $attendance = new Attendance([
            'date' => '2026-09-21',
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
        ]);

        $this->assertSame(480, $attendance->regularWorkedMinutes());
    }

    public function test_past_unfinished_attendance_is_flagged_for_a_clock_out_correction(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'time_in' => '08:00:00',
            'status' => 'present',
        ]);
        $this->travelTo('2026-09-22 09:00:00');

        $this->actingAs($user)
            ->get(route('attendance.daily'))
            ->assertOk()
            ->assertSee('Missing clock out')
            ->assertSee('Pending verification')
            ->assertSee('Correct time out')
            ->assertSee(route('attendance.corrections', ['attendance_id' => $attendance->id]));
    }

    public function test_late_employee_cannot_start_overtime(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        Attendance::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'time_in' => '08:01:00',
            'time_out' => '17:00:00',
            'status' => 'late',
        ]);
        $this->travelTo('2026-09-21 17:30:00');

        $this->actingAs($user)
            ->patch(route('attendance.overtime.start'))
            ->assertRedirect()
            ->assertSessionHas('attendance_error', 'Overtime is available only when you clocked in at 8:00 AM or earlier.');
    }

    public function test_eligible_employee_can_start_overtime_after_the_required_interval(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
            'status' => 'present',
        ]);
        $this->travelTo('2026-09-21 17:30:00');

        $this->actingAs($user)
            ->patch(route('attendance.overtime.start'))
            ->assertRedirect(route('attendance.time-in-out'));

        $this->assertDatabaseHas('attendance', [
            'id' => $attendance->id,
            'overtime_in' => '17:30:00',
        ]);
    }

    public function test_overtime_cannot_end_before_the_two_hour_minimum(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        Attendance::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
            'overtime_in' => '17:30:00',
            'status' => 'present',
        ]);
        $this->travelTo('2026-09-21 19:29:00');

        $this->actingAs($user)
            ->patch(route('attendance.overtime.end'))
            ->assertRedirect()
            ->assertSessionHas('attendance_error', 'Overtime can be ended from 7:30 PM to meet the two-hour minimum.');
    }

    public function test_overtime_is_capped_at_eight_thirty_pm(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
            'overtime_in' => '17:30:00',
            'status' => 'present',
        ]);
        $this->travelTo('2026-09-21 20:45:00');

        $this->actingAs($user)
            ->patch(route('attendance.overtime.end'))
            ->assertRedirect(route('attendance.time-in-out'))
            ->assertSessionHas('attendance_success', 'Overtime ended and was capped at the 8:30 PM maximum.');

        $this->assertDatabaseHas('attendance', [
            'id' => $attendance->id,
            'overtime_out' => '20:30:00',
        ]);
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
}
