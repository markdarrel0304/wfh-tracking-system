<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleManagementController extends Controller
{
    /** @var array<string, array{label: string, description: string, permissions: list<string>}> */
    private const ROLE_DETAILS = [
        'employee' => [
            'label' => 'Employee',
            'description' => 'Manages their own workday, remote-work requests, tasks, reports, and uploaded output.',
            'permissions' => [
                'Submit and track personal WFH requests',
                'Record time in, time out, and overtime',
                'Submit attendance corrections with supporting proof',
                'Create daily tasks, accomplishment reports, and output attachments',
                'View only their own attendance and accomplishment records',
            ],
        ],
        'supervisor' => [
            'label' => 'Supervisor',
            'description' => 'Reviews employee submissions while retaining employee access to their own workspace.',
            'permissions' => [
                'All Employee permissions',
                'Review WFH requests',
                'Review attendance correction requests and supporting proof',
                'Review accomplishment reports and request revisions',
                'View documents needed for assigned approval work',
            ],
        ],
        'admin' => [
            'label' => 'Administrator',
            'description' => 'Controls workforce administration, reports, approvals, and account access across the system.',
            'permissions' => [
                'All Supervisor permissions',
                'Manage users, roles, departments, schedules, and holidays',
                'View company-wide attendance, WFH, and accomplishment reports',
                'Export payroll and timekeeping information',
                'View audit logs and manage account access',
            ],
        ],
    ];

    public function index(Request $request): View
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $userCounts = User::query()
            ->selectRaw('role, count(*) as total, sum(is_active) as active_total')
            ->whereIn('role', array_keys(self::ROLE_DETAILS))
            ->groupBy('role')
            ->get()
            ->keyBy('role');

        $roles = collect(self::ROLE_DETAILS)->map(function (array $details, string $role) use ($userCounts): array {
            $counts = $userCounts->get($role);

            return [
                'key' => $role,
                ...$details,
                'total_users' => (int) ($counts->total ?? 0),
                'active_users' => (int) ($counts->active_total ?? 0),
            ];
        });

        return view('admin.roles', [
            'roles' => $roles,
            'recentUsersByRole' => User::query()
                ->whereIn('role', array_keys(self::ROLE_DETAILS))
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role', 'is_active'])
                ->groupBy('role'),
        ]);
    }
}
