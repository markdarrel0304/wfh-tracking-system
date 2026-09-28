<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\WfhRequest;
use App\Models\WorkSchedule;
use App\Notifications\WfhRequestReviewed;
use App\Notifications\WfhRequestSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WfhApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_a_pending_wfh_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $approver = $this->createEmployeeFor($admin);
        $employee = $this->createEmployeeFor(User::factory()->create());
        $wfhRequest = $this->createPendingRequestFor($employee);

        $response = $this->actingAs($admin)->patch(route('wfh.requests.approval', $wfhRequest), [
            'status' => 'approved',
            'remarks' => 'Approved for the requested work days.',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('wfh.approval'));

        $this->assertDatabaseHas('wfh_requests', [
            'id' => $wfhRequest->id,
            'status' => 'approved',
            'approver_id' => $approver->id,
            'remarks' => 'Approved for the requested work days.',
        ]);
        $this->assertNotNull($wfhRequest->fresh()->approved_at);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'event' => 'wfh_request.approved',
            'auditable_id' => $wfhRequest->id,
        ]);
    }

    public function test_admin_can_reject_a_pending_wfh_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $approver = $this->createEmployeeFor($admin);
        $employee = $this->createEmployeeFor(User::factory()->create());
        $wfhRequest = $this->createPendingRequestFor($employee);

        $this->actingAs($admin)
            ->patch(route('wfh.requests.approval', $wfhRequest), [
                'status' => 'rejected',
                'remarks' => 'Please submit a request with dates two working days in advance.',
            ])
            ->assertRedirect(route('wfh.approval'));

        $this->assertDatabaseHas('wfh_requests', [
            'id' => $wfhRequest->id,
            'status' => 'rejected',
            'approver_id' => $approver->id,
            'remarks' => 'Please submit a request with dates two working days in advance.',
            'approved_at' => null,
        ]);
    }

    public function test_admin_can_attach_a_supporting_document_when_reviewing_a_wfh_request(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->createEmployeeFor($admin);
        $employee = $this->createEmployeeFor(User::factory()->create(['role' => 'employee']));
        $wfhRequest = $this->createPendingRequestFor($employee);

        $this->actingAs($admin)
            ->patch(route('wfh.requests.approval', $wfhRequest), [
                'status' => 'approved',
                'reviewer_document' => UploadedFile::fake()->create('review-note.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('wfh.approval'));

        $reviewerDocument = $wfhRequest->fresh()->reviewer_document;

        $this->assertNotNull($reviewerDocument);
        Storage::disk('local')->assertExists($reviewerDocument);
    }

    public function test_regular_employee_cannot_access_wfh_approval_queue(): void
    {
        $employeeUser = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employeeUser)
            ->get(route('wfh.approval'))
            ->assertForbidden();
    }

    public function test_supervisor_can_access_the_wfh_approval_queue(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);

        $this->actingAs($supervisor)
            ->get(route('wfh.approval'))
            ->assertOk();
    }

    public function test_approver_can_open_a_pending_request_for_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = $this->createEmployeeFor(User::factory()->create(['role' => 'employee']));
        $wfhRequest = $this->createPendingRequestFor($employee);

        $this->actingAs($admin)
            ->get(route('wfh.approval.review', $wfhRequest))
            ->assertOk()
            ->assertSee('Review remote-work request')
            ->assertSee($wfhRequest->reason);
    }

    public function test_regular_employee_cannot_open_a_pending_request_for_review(): void
    {
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($employeeUser);
        $wfhRequest = $this->createPendingRequestFor($employee);

        $this->actingAs($employeeUser)
            ->get(route('wfh.approval.review', $wfhRequest))
            ->assertForbidden();
    }

    public function test_submitting_a_wfh_request_notifies_admins_and_supervisors(): void
    {
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $this->createEmployeeFor($employeeUser);
        $admin = User::factory()->create(['role' => 'admin']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);

        Notification::fake();

        $this->actingAs($employeeUser)
            ->post(route('wfh.requests.store'), [
                'request_type' => 'Work From Home',
                'date_from' => '2026-09-28',
                'date_to' => '2026-09-28',
                'start_time' => '08:00',
                'end_time' => '17:00',
                'reason' => 'Focused work from home.',
            ])
            ->assertRedirect(route('wfh.my-requests'));

        Notification::assertSentTo(
            [$admin, $supervisor],
            WfhRequestSubmitted::class,
            fn (WfhRequestSubmitted $notification): bool => $notification->wfhRequest->employee_id === $employeeUser->employee->id,
        );
    }

    public function test_employee_sees_a_submission_confirmation_on_my_requests(): void
    {
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $this->createEmployeeFor($employeeUser);

        $this->actingAs($employeeUser)
            ->post(route('wfh.requests.store'), [
                'request_type' => 'Work From Home',
                'date_from' => '2026-09-28',
                'date_to' => '2026-09-28',
                'start_time' => '08:00',
                'end_time' => '17:00',
                'reason' => 'Focused work from home.',
            ])
            ->assertRedirect(route('wfh.my-requests'))
            ->assertSessionHas('success', 'Your WFH request was sent for review.')
            ->assertSessionHas('submitted_request_id');

        $this->get(route('wfh.my-requests'))
            ->assertSee('Request sent successfully')
            ->assertSee('Your WFH request was sent for review.')
            ->assertSee('Pending');
    }

    public function test_employee_cannot_submit_an_overlapping_wfh_request(): void
    {
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($employeeUser);
        $this->createPendingRequestFor($employee)->update(['status' => 'approved']);

        $this->actingAs($employeeUser)
            ->post(route('wfh.requests.store'), [
                'request_type' => 'Work From Home',
                'date_from' => '2026-09-28',
                'date_to' => '2026-09-29',
                'start_time' => '08:00',
                'end_time' => '17:00',
                'reason' => 'Overlapping remote work.',
            ])
            ->assertSessionHasErrors('date_from');

        $this->assertSame(1, $employee->wfhRequests()->count());
    }

    public function test_only_employees_can_open_the_wfh_request_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createEmployeeFor($admin);

        $this->actingAs($admin)
            ->get(route('wfh.requests.create'))
            ->assertForbidden();
    }

    public function test_wfh_documents_are_downloaded_from_private_storage_by_authorized_users(): void
    {
        Storage::fake('local');
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($employeeUser);
        $wfhRequest = $this->createPendingRequestFor($employee);
        $wfhRequest->update(['supporting_document' => 'wfh_documents/private-proof.pdf']);
        Storage::disk('local')->put('wfh_documents/private-proof.pdf', 'private proof');

        $this->actingAs($employeeUser)
            ->get(route('wfh.requests.supporting-document', $wfhRequest))
            ->assertOk();

        $otherEmployee = User::factory()->create(['role' => 'employee']);
        $this->createEmployeeFor($otherEmployee);

        $this->actingAs($otherEmployee)
            ->get(route('wfh.requests.supporting-document', $wfhRequest))
            ->assertForbidden();
    }

    public function test_approving_a_wfh_request_notifies_the_employee(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createEmployeeFor($admin);
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = $this->createEmployeeFor($employeeUser);
        $wfhRequest = $this->createPendingRequestFor($employee);

        Notification::fake();

        $this->actingAs($admin)
            ->patch(route('wfh.requests.approval', $wfhRequest), [
                'status' => 'approved',
            ])
            ->assertRedirect(route('wfh.approval'));

        Notification::assertSentTo(
            $employeeUser,
            WfhRequestReviewed::class,
            fn (WfhRequestReviewed $notification): bool => $notification->wfhRequest->is($wfhRequest)
                && $notification->wfhRequest->status === 'approved',
        );
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

    private function createPendingRequestFor(Employee $employee): WfhRequest
    {
        return WfhRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'Work From Home',
            'date_from' => '2026-09-28',
            'date_to' => '2026-09-28',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'reason' => 'Focused work from home.',
            'status' => 'pending',
        ]);
    }
}
