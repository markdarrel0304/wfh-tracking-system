<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Notifications\AttendanceCorrectionReviewed;
use App\Notifications\AttendanceCorrectionSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceCorrectionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_a_correction_for_their_own_attendance(): void
    {
        Notification::fake();
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($employeeUser);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->createEmployeeFor($supervisor);
        $attendance = $this->createAttendanceFor($employee, '08:00:00', '17:00:00');
        $this->travelTo('2026-09-22 09:00:00');

        $this->actingAs($employeeUser)->post(route('attendance.corrections.store'), [
            'attendance_id' => $attendance->id,
            'requested_time_in' => '07:55',
            'requested_time_out' => '17:00',
            'reason' => 'The time-in scanner was unavailable.',
        ])->assertRedirect(route('attendance.corrections'))->assertSessionHas('correction_success');

        $this->assertDatabaseHas('attendance_corrections', ['attendance_id' => $attendance->id, 'employee_id' => $employee->id, 'requested_time_in' => '07:55:00', 'status' => 'pending']);
        Notification::assertSentTo($supervisor, AttendanceCorrectionSubmitted::class);
    }

    public function test_employee_cannot_submit_a_correction_for_another_employees_attendance(): void
    {
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $this->createEmployeeFor($employeeUser);
        $otherEmployee = $this->createEmployeeFor(User::factory()->create(['role' => 'employee']));
        $attendance = $this->createAttendanceFor($otherEmployee, '08:00:00', '17:00:00');

        $this->actingAs($employeeUser)->post(route('attendance.corrections.store'), [
            'attendance_id' => $attendance->id,
            'requested_time_in' => '07:55',
            'reason' => 'Invalid request.',
        ])->assertNotFound();
    }

    public function test_supervisor_approval_updates_attendance_and_notifies_the_employee(): void
    {
        Notification::fake();
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($employeeUser);
        $supervisorUser = User::factory()->create(['role' => 'supervisor']);
        $supervisor = $this->createEmployeeFor($supervisorUser);
        $attendance = $this->createAttendanceFor($employee, '08:15:00', '17:00:00', 'late');
        $correction = AttendanceCorrection::create(['attendance_id' => $attendance->id, 'employee_id' => $employee->id, 'requested_time_in' => '07:55:00', 'requested_time_out' => '17:10:00', 'reason' => 'The clock-in terminal was offline.', 'status' => 'pending']);

        $this->actingAs($supervisorUser)->patch(route('approvals.attendance-corrections.update', $correction), [
            'status' => 'approved',
            'remarks' => 'Verified against the supervisor log.',
        ])->assertRedirect(route('approvals.attendance-corrections'))->assertSessionHas('approval_success');

        $this->assertDatabaseHas('attendance', ['id' => $attendance->id, 'time_in' => '07:55:00', 'time_out' => '17:10:00', 'status' => 'present']);
        $this->assertDatabaseHas('attendance_corrections', ['id' => $correction->id, 'status' => 'approved', 'approver_id' => $supervisor->id]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $supervisorUser->id,
            'event' => 'attendance_correction.approved',
            'auditable_id' => $correction->id,
        ]);
        Notification::assertSentTo($employeeUser, AttendanceCorrectionReviewed::class);
    }

    public function test_supervisor_can_open_a_pending_correction_for_review(): void
    {
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($employeeUser);
        $supervisorUser = User::factory()->create(['role' => 'supervisor']);
        $this->createEmployeeFor($supervisorUser);
        $attendance = $this->createAttendanceFor($employee, '08:00:00', '17:00:00');
        $correction = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'requested_time_in' => '07:55:00',
            'reason' => 'The clock-in terminal was offline.',
            'status' => 'pending',
        ]);

        $this->actingAs($supervisorUser)
            ->get(route('approvals.attendance-corrections.review', $correction))
            ->assertOk()
            ->assertSee('Review attendance correction')
            ->assertSee($correction->reason);
    }

    public function test_employee_cannot_open_a_pending_correction_for_review(): void
    {
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($employeeUser);
        $attendance = $this->createAttendanceFor($employee, '08:00:00', '17:00:00');
        $correction = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'requested_time_in' => '07:55:00',
            'reason' => 'The clock-in terminal was offline.',
            'status' => 'pending',
        ]);

        $this->actingAs($employeeUser)
            ->get(route('approvals.attendance-corrections.review', $correction))
            ->assertForbidden();
    }

    public function test_rejection_requires_a_review_note_and_keeps_attendance_unchanged(): void
    {
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($employeeUser);
        $supervisorUser = User::factory()->create(['role' => 'supervisor']);
        $this->createEmployeeFor($supervisorUser);
        $attendance = $this->createAttendanceFor($employee, '08:00:00', '17:00:00');
        $correction = AttendanceCorrection::create(['attendance_id' => $attendance->id, 'employee_id' => $employee->id, 'requested_time_in' => '07:55:00', 'reason' => 'Requested change.', 'status' => 'pending']);

        $this->actingAs($supervisorUser)->patch(route('approvals.attendance-corrections.update', $correction), ['status' => 'rejected'])->assertSessionHasErrors('remarks');

        $this->assertDatabaseHas('attendance', ['id' => $attendance->id, 'time_in' => '08:00:00']);
        $this->assertDatabaseHas('attendance_corrections', ['id' => $correction->id, 'status' => 'pending']);
    }

    public function test_correction_proof_is_only_downloadable_by_the_owner_or_reviewer(): void
    {
        Storage::fake('local');
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($employeeUser);
        $attendance = $this->createAttendanceFor($employee, '08:00:00', '17:00:00');
        $correction = AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'requested_time_in' => '07:55:00',
            'reason' => 'The clock-in terminal was offline.',
            'supporting_document' => 'attendance_corrections/private-proof.pdf',
            'status' => 'pending',
        ]);
        Storage::disk('local')->put('attendance_corrections/private-proof.pdf', 'private proof');

        $this->actingAs($employeeUser)
            ->get(route('attendance.corrections.supporting-document', $correction))
            ->assertOk();

        $otherEmployeeUser = User::factory()->create(['role' => 'employee']);
        $this->createEmployeeFor($otherEmployeeUser);

        $this->actingAs($otherEmployeeUser)
            ->get(route('attendance.corrections.supporting-document', $correction))
            ->assertForbidden();
    }

    private function createEmployeeFor(User $user): Employee
    {
        $department = Department::firstOrCreate(['name' => 'Information Technology']);
        $workSchedule = WorkSchedule::firstOrCreate(['name' => 'Regular'], ['days_json' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']), 'time_in' => '08:00:00', 'time_out' => '17:00:00']);

        return Employee::create(['user_id' => $user->id, 'employee_number' => 'EMP-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT), 'first_name' => 'Test', 'last_name' => 'Employee', 'department_id' => $department->id, 'position' => 'Employee', 'work_schedule_id' => $workSchedule->id, 'date_hired' => '2025-01-01', 'status' => 'active']);
    }

    private function createAttendanceFor(Employee $employee, string $timeIn, string $timeOut, string $status = 'present'): Attendance
    {
        return Attendance::create(['employee_id' => $employee->id, 'date' => '2026-09-21', 'time_in' => $timeIn, 'time_out' => $timeOut, 'status' => $status]);
    }
}
