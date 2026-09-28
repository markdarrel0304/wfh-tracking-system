<?php

namespace Tests\Feature;

use App\Models\AccomplishmentReport;
use App\Models\DailyTask;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Notifications\AccomplishmentReportReviewed;
use App\Notifications\AccomplishmentReportRevisionRequested;
use App\Notifications\AccomplishmentReportSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccomplishmentReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_a_daily_accomplishment_report(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $task = $this->createCompletedTaskFor($employee);
        $admin = User::factory()->create(['role' => 'admin']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        Notification::fake();

        $this->actingAs($user)
            ->post(route('accomplishments.reports.store'), [
                'date' => '2026-09-21',
                'daily_task_id' => $task->id,
                'summary' => 'Completed the daily task workflow and verified the approval path.',
                'blockers' => 'No blockers today.',
                'next_steps' => 'Prepare the next day priorities.',
            ])
            ->assertRedirect(route('accomplishments.reports', ['date' => '2026-09-21']).'#history')
            ->assertSessionHas('report_success');

        $this->assertDatabaseHas('accomplishment_reports', [
            'employee_id' => $employee->id,
            'date' => '2026-09-21 00:00:00',
            'status' => 'submitted',
        ]);
        Notification::assertSentTo([$admin, $supervisor], AccomplishmentReportSubmitted::class);
    }

    public function test_employee_can_submit_a_direct_accomplishment_without_creating_a_task(): void
    {
        Notification::fake();
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->post(route('accomplishments.reports.store'), [
                'date' => '2026-09-21',
                'title' => 'Finished attendance report module',
                'category' => 'development',
                'progress_status' => 'completed',
                'summary' => 'Completed the attendance report module and checked the output.',
            ])
            ->assertRedirect(route('accomplishments.reports', ['date' => '2026-09-21']).'#history');

        $this->assertDatabaseHas('accomplishment_reports', [
            'employee_id' => $employee->id,
            'daily_task_id' => null,
            'title' => 'Finished attendance report module',
            'category' => 'development',
            'progress_status' => 'completed',
        ]);
        Notification::assertSentTo($admin, AccomplishmentReportSubmitted::class);
    }

    public function test_employee_sees_the_direct_accomplishment_form_without_task_creation(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $this->createEmployeeFor($user);

        $this->actingAs($user)
            ->get(route('accomplishments.reports', ['date' => '2026-09-21']))
            ->assertOk()
            ->assertSee('What did you accomplish?')
            ->assertSee('Save draft')
            ->assertDontSee('Create a task');
    }

    public function test_admin_queue_displays_a_submitted_direct_accomplishment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = $this->createEmployeeFor(User::factory()->create(['role' => 'employee']));
        AccomplishmentReport::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'title' => 'Finished attendance report module',
            'category' => 'development',
            'progress_status' => 'completed',
            'summary' => 'Completed the attendance report module and checked the output.',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('approvals.accomplishment-reports'))
            ->assertSee('Finished attendance report module')
            ->assertSee('Accomplishments awaiting review');
    }

    public function test_employee_can_save_an_accomplishment_draft_without_sending_it_for_review(): void
    {
        Notification::fake();
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $task = $this->createCompletedTaskFor($employee);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createEmployeeFor($admin);

        $this->actingAs($user)
            ->post(route('accomplishments.reports.store'), [
                'date' => '2026-09-21',
                'daily_task_id' => $task->id,
                'summary' => 'Draft notes for the completed daily task.',
                'submission_action' => 'draft',
            ])
            ->assertRedirect(route('accomplishments.reports', ['date' => '2026-09-21']).'#history')
            ->assertSessionHas('report_success', 'Your accomplishment draft was saved. You can submit it for review when it is ready.');

        $this->assertDatabaseHas('accomplishment_reports', [
            'employee_id' => $employee->id,
            'daily_task_id' => $task->id,
            'summary' => 'Draft notes for the completed daily task.',
            'status' => 'submitted',
            'submitted_at' => null,
        ]);
        Notification::assertNothingSent();

        $this->actingAs($admin)
            ->get(route('approvals.accomplishment-reports'))
            ->assertOk()
            ->assertDontSee('Draft notes for the completed daily task.');
    }

    public function test_employee_updates_their_existing_report_for_the_same_day(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $task = $this->createCompletedTaskFor($employee);
        AccomplishmentReport::create([
            'employee_id' => $employee->id,
            'daily_task_id' => $task->id,
            'date' => '2026-09-21 00:00:00',
            'summary' => 'Initial report.',
            'status' => 'submitted',
            'submitted_at' => now()->subHour(),
        ]);

        $this->actingAs($user)
            ->post(route('accomplishments.reports.store'), [
                'date' => '2026-09-21',
                'daily_task_id' => $task->id,
                'summary' => 'Updated report with final accomplishments.',
                'blockers' => null,
                'next_steps' => 'Share the completed work with the team.',
            ])
            ->assertRedirect(route('accomplishments.reports', ['date' => '2026-09-21']).'#history');

        $this->assertDatabaseCount('accomplishment_reports', 1);
        $this->assertDatabaseHas('accomplishment_reports', [
            'employee_id' => $employee->id,
            'date' => '2026-09-21 00:00:00',
            'summary' => 'Updated report with final accomplishments.',
        ]);
    }

    public function test_employee_can_correct_a_reviewed_report_and_resubmit_it_without_reopening_tasks(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $reviewer = $this->createEmployeeFor(User::factory()->create(['role' => 'admin']));
        $task = $this->createCompletedTaskFor($employee);
        $report = AccomplishmentReport::create([
            'employee_id' => $employee->id,
            'daily_task_id' => $task->id,
            'date' => '2026-09-21',
            'summary' => 'Initial report.',
            'status' => 'reviewed',
            'submitted_at' => now()->subHours(2),
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now()->subHour(),
            'review_note' => 'Reviewed already.',
        ]);

        $this->actingAs($user)
            ->post(route('accomplishments.reports.store'), [
                'date' => '2026-09-21',
                'daily_task_id' => $task->id,
                'summary' => 'Corrected report with complete information.',
                'blockers' => 'No blockers.',
                'next_steps' => 'Share the completed work.',
            ])
            ->assertRedirect(route('accomplishments.reports', ['date' => '2026-09-21']).'#history');

        $this->assertDatabaseCount('accomplishment_reports', 1);
        $this->assertDatabaseHas('accomplishment_reports', [
            'id' => $report->id,
            'summary' => 'Corrected report with complete information.',
            'status' => 'submitted',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ]);
    }

    public function test_employee_can_submit_completed_work_and_supporting_files_with_one_accomplishment(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $task = $this->createCompletedTaskFor($employee);

        $this->actingAs($user)
            ->post(route('accomplishments.reports.store'), [
                'date' => '2026-09-21',
                'daily_task_id' => $task->id,
                'summary' => 'Completed the weekly summary and uploaded the final document.',
                'attachments' => [UploadedFile::fake()->create('weekly-summary.pdf', 100, 'application/pdf')],
            ])
            ->assertRedirect(route('accomplishments.reports', ['date' => '2026-09-21']).'#history')
            ->assertSessionHas('report_success', 'Your accomplishment was submitted for review with 1 attachment(s).');

        $report = AccomplishmentReport::firstOrFail();
        $this->assertDatabaseHas('output_attachments', [
            'accomplishment_report_id' => $report->id,
            'daily_task_id' => $task->id,
            'file_name' => 'weekly-summary.pdf',
        ]);
        Storage::disk('local')->assertExists($report->outputAttachments()->sole()->file_path);

        $this->get(route('accomplishments.reports', ['date' => '2026-09-21', 'edit' => $report->id]))
            ->assertOk()
            ->assertSee('weekly-summary.pdf');
    }

    public function test_employee_cannot_open_another_employees_accomplishment_for_editing(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $otherEmployee = $this->createEmployeeFor(User::factory()->create(['role' => 'employee']));
        $otherReport = AccomplishmentReport::create([
            'employee_id' => $otherEmployee->id,
            'date' => '2026-09-21',
            'title' => 'Private accomplishment',
            'summary' => 'This entry belongs to another employee.',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('accomplishments.reports', ['date' => '2026-09-21', 'edit' => $otherReport->id]))
            ->assertNotFound();
    }

    public function test_employee_can_submit_separate_accomplishments_for_multiple_completed_tasks_on_the_same_day(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $firstTask = $this->createCompletedTaskFor($employee);
        $secondTask = DailyTask::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'title' => 'Review the completed report',
            'priority' => 'normal',
            'status' => 'done',
            'completed_at' => now(),
        ]);

        foreach ([$firstTask, $secondTask] as $task) {
            $this->actingAs($user)
                ->post(route('accomplishments.reports.store'), [
                    'date' => '2026-09-21',
                    'daily_task_id' => $task->id,
                    'summary' => 'Completed '.$task->title.'.',
                ])
                ->assertRedirect(route('accomplishments.reports', ['date' => '2026-09-21']).'#history');
        }

        $this->assertDatabaseCount('accomplishment_reports', 2);
        $this->assertDatabaseHas('accomplishment_reports', ['daily_task_id' => $firstTask->id]);
        $this->assertDatabaseHas('accomplishment_reports', ['daily_task_id' => $secondTask->id]);
    }

    public function test_user_without_an_employee_profile_cannot_submit_a_report(): void
    {
        $user = User::factory()->create(['role' => 'employee']);

        $this->actingAs($user)
            ->post(route('accomplishments.reports.store'), [
                'date' => '2026-09-21',
                'summary' => 'This report should not be stored.',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_review_a_submitted_accomplishment_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $approver = $this->createEmployeeFor($admin);
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($employeeUser);
        $report = AccomplishmentReport::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'summary' => 'Completed the assigned work.',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Notification::fake();

        $this->actingAs($admin)
            ->patch(route('approvals.accomplishment-reports.update', $report), [
                'review_note' => 'Work and supporting details have been checked.',
            ])
            ->assertRedirect(route('approvals.accomplishment-reports'))
            ->assertSessionHas('approval_success');

        $this->assertDatabaseHas('accomplishment_reports', [
            'id' => $report->id,
            'status' => 'reviewed',
            'reviewed_by' => $approver->id,
            'review_note' => 'Work and supporting details have been checked.',
        ]);
        Notification::assertSentTo($employeeUser, AccomplishmentReportReviewed::class);
    }

    public function test_employee_cannot_review_an_accomplishment_report(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $report = AccomplishmentReport::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'summary' => 'Completed the assigned work.',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('approvals.accomplishment-reports.update', $report), [
                'review_note' => 'This must not be accepted.',
            ])
            ->assertForbidden();
    }

    public function test_supervisor_can_request_changes_to_a_submitted_report_with_feedback(): void
    {
        Notification::fake();
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->createEmployeeFor($supervisor);
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($employeeUser);
        $report = AccomplishmentReport::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'summary' => 'Completed the assigned work.',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($supervisor)
            ->patch(route('approvals.accomplishment-reports.request-revision', $report), [
                'review_note' => 'Please explain the test results and attach the final screenshot.',
            ])
            ->assertRedirect(route('approvals.accomplishment-reports'))
            ->assertSessionHas('approval_success');

        $this->assertDatabaseHas('accomplishment_reports', [
            'id' => $report->id,
            'status' => 'submitted',
            'review_note' => 'Please explain the test results and attach the final screenshot.',
        ]);
        $this->assertNotNull($report->fresh()->revision_requested_at);
        Notification::assertSentTo($employeeUser, AccomplishmentReportRevisionRequested::class);
    }

    public function test_employee_sees_requested_changes_and_the_reviewer_note(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $task = $this->createCompletedTaskFor($employee);
        $report = AccomplishmentReport::create([
            'employee_id' => $employee->id,
            'daily_task_id' => $task->id,
            'date' => '2026-09-21',
            'summary' => 'Initial accomplishment details.',
            'status' => 'submitted',
            'submitted_at' => now(),
            'review_note' => 'Please attach the final screenshot.',
            'revision_requested_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('accomplishments.reports', ['date' => '2026-09-21']))
            ->assertOk()
            ->assertSee('Changes requested');

        $this->get(route('accomplishments.reports', ['date' => '2026-09-21', 'edit' => $report->id]))
            ->assertOk()
            ->assertSee('Changes requested')
            ->assertSee('Please attach the final screenshot.')
            ->assertSee('Update and submit');
    }

    public function test_revision_request_requires_a_supervisor_note(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->createEmployeeFor($supervisor);
        $employee = $this->createEmployeeFor(User::factory()->create(['role' => 'employee']));
        $report = AccomplishmentReport::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'summary' => 'Completed the assigned work.',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($supervisor)
            ->from(route('approvals.accomplishment-reports'))
            ->patch(route('approvals.accomplishment-reports.request-revision', $report), [])
            ->assertRedirect(route('approvals.accomplishment-reports'))
            ->assertSessionHasErrors('review_note');
    }

    private function createEmployeeFor(User $user): Employee
    {
        $department = Department::firstOrCreate(['name' => 'Information Technology']);
        $workSchedule = WorkSchedule::firstOrCreate(
            ['name' => 'Regular'],
            ['days_json' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']), 'time_in' => '08:00:00', 'time_out' => '17:00:00']
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

    private function createCompletedTaskFor(Employee $employee, string $date = '2026-09-21'): DailyTask
    {
        return DailyTask::create([
            'employee_id' => $employee->id,
            'date' => $date,
            'title' => 'Prepare weekly summary',
            'task_description' => 'Compiled the completed work for the team.',
            'priority' => 'high',
            'status' => 'done',
            'completed_at' => now(),
        ]);
    }
}
