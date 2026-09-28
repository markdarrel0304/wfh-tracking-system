<?php

namespace Tests\Feature;

use App\Models\AccomplishmentReport;
use App\Models\DailyTask;
use App\Models\Department;
use App\Models\Employee;
use App\Models\OutputAttachment;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OutputAttachmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_upload_an_output_attachment_to_their_report(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $report = $this->createReportFor($employee);

        $this->actingAs($user)
            ->from(route('accomplishments.attachments'))
            ->post(route('accomplishments.attachments.store'), [
                'accomplishment_report_id' => $report->id,
                'attachments' => [UploadedFile::fake()->create('weekly-summary.pdf', 100, 'application/pdf')],
            ])
            ->assertRedirect(route('accomplishments.attachments'))
            ->assertSessionHas('attachment_success');

        $attachment = OutputAttachment::firstOrFail();
        $this->assertDatabaseHas('output_attachments', [
            'accomplishment_report_id' => $report->id,
            'file_name' => 'weekly-summary.pdf',
        ]);
        Storage::disk('local')->assertExists($attachment->file_path);
    }

    public function test_employee_cannot_upload_to_another_employees_report(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => 'employee']);
        $this->createEmployeeFor($user);
        $otherReport = $this->createReportFor($this->createEmployeeFor(User::factory()->create(['role' => 'employee'])));

        $this->actingAs($user)
            ->from(route('accomplishments.attachments'))
            ->post(route('accomplishments.attachments.store'), [
                'accomplishment_report_id' => $otherReport->id,
                'attachments' => [UploadedFile::fake()->create('private-report.pdf', 100, 'application/pdf')],
            ])
            ->assertRedirect(route('accomplishments.attachments'))
            ->assertSessionHasErrors('accomplishment_report_id');

        $this->assertDatabaseCount('output_attachments', 0);
    }

    public function test_employee_can_link_an_output_attachment_to_a_task_from_the_same_report_workday(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $report = $this->createReportFor($employee);
        $task = DailyTask::create([
            'employee_id' => $employee->id,
            'date' => $report->date,
            'title' => 'Prepare weekly summary',
            'priority' => 'normal',
            'status' => 'done',
        ]);

        $this->actingAs($user)
            ->post(route('accomplishments.attachments.store'), [
                'accomplishment_report_id' => $report->id,
                'daily_task_id' => $task->id,
                'attachments' => [UploadedFile::fake()->create('weekly-summary.pdf', 100, 'application/pdf')],
            ])
            ->assertRedirect(route('accomplishments.attachments'));

        $this->assertDatabaseHas('output_attachments', [
            'accomplishment_report_id' => $report->id,
            'daily_task_id' => $task->id,
        ]);
    }

    public function test_employee_cannot_link_an_output_attachment_to_a_task_from_another_workday(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($user);
        $report = $this->createReportFor($employee);
        $task = DailyTask::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-22',
            'title' => 'Prepare tomorrow\'s weekly summary',
            'priority' => 'normal',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->from(route('accomplishments.attachments'))
            ->post(route('accomplishments.attachments.store'), [
                'accomplishment_report_id' => $report->id,
                'daily_task_id' => $task->id,
                'attachments' => [UploadedFile::fake()->create('weekly-summary.pdf', 100, 'application/pdf')],
            ])
            ->assertRedirect(route('accomplishments.attachments'))
            ->assertSessionHasErrors('daily_task_id');

        $this->assertDatabaseCount('output_attachments', 0);
    }

    public function test_employee_cannot_download_another_employees_attachment(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => 'employee']);
        $this->createEmployeeFor($user);
        $otherReport = $this->createReportFor($this->createEmployeeFor(User::factory()->create(['role' => 'employee'])));
        Storage::disk('local')->put('output_attachments/'.$otherReport->id.'/private-report.pdf', 'private content');
        $attachment = OutputAttachment::create([
            'accomplishment_report_id' => $otherReport->id,
            'file_path' => 'output_attachments/'.$otherReport->id.'/private-report.pdf',
            'file_name' => 'private-report.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 15,
            'uploaded_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('accomplishments.attachments.download', $attachment))
            ->assertForbidden();
    }

    private function createReportFor(Employee $employee): AccomplishmentReport
    {
        return AccomplishmentReport::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-21',
            'summary' => 'Completed the assigned work.',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
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
}
