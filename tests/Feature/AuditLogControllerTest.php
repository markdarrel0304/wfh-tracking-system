<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_and_view_an_audit_log_entry(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['role' => 'employee']);
        AuditLog::create([
            'user_id' => $admin->id,
            'event' => 'wfh_request.approved',
            'auditable_type' => 'App\\Models\\WfhRequest',
            'auditable_id' => 42,
            'summary' => 'Approved a Work From Home request.',
            'metadata' => ['employee' => $employee->name, 'new_status' => 'approved'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test browser',
        ]);

        $this->actingAs($admin)
            ->get(route('audit-logs.index', ['event' => 'wfh_request.approved']))
            ->assertOk()
            ->assertSee('Approved a Work From Home request.')
            ->assertSee($employee->name)
            ->assertSee('127.0.0.1');
    }

    public function test_employee_cannot_access_audit_logs(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)
            ->get(route('audit-logs.index'))
            ->assertForbidden();
    }
}
