<?php

namespace App\Http\Controllers;

use App\AuditLogger;
use App\Http\Requests\ResetManagedUserPasswordRequest;
use App\Http\Requests\StoreManagedUserRequest;
use App\Http\Requests\UpdateManagedUserRequest;
use App\Models\Department;
use App\Models\User;
use App\Notifications\AccountSettingsUpdated;
use App\Notifications\PasswordResetByAdministrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'in:employee,supervisor,admin'],
            'status' => ['nullable', 'in:active,inactive'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        $users = User::query()
            ->with('employee:id,user_id,employee_number,first_name,last_name')
            ->with('department:id,name')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($userQuery) use ($search): void {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('employee', fn ($employeeQuery) => $employeeQuery->where('employee_number', 'like', "%{$search}%"));
                });
            })
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('is_active', $status === 'active'))
            ->when($filters['department_id'] ?? null, fn ($query, int $departmentId) => $query->where('department_id', $departmentId))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users', [
            'users' => $users,
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function store(StoreManagedUserRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        $auditLogger->record($request->user(), 'user.created', $user, "Created the {$user->role} account for {$user->name}.", [
            'role' => $user->role,
            'department_id' => $user->department_id,
        ], $request);

        return back()->with('success', 'User account created successfully.');
    }

    public function update(UpdateManagedUserRequest $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $originalSettings = $user->only(['role', 'department_id', 'is_active']);
        $this->ensureCurrentAdminKeepsAccess($request, $user, $data);
        $this->ensureAnotherActiveAdminRemains($user, $data['role'], (bool) $data['is_active']);
        $user->update($data);

        $auditLogger->record($request->user(), 'user.updated', $user, "Updated the account settings for {$user->name}.", [
            'role' => $user->role,
            'department_id' => $user->department_id,
            'is_active' => $user->is_active,
        ], $request);

        $changedSettings = [];

        if ($originalSettings['role'] !== $user->role) {
            $changedSettings[] = 'role';
        }
        if ((int) $originalSettings['department_id'] !== (int) $user->department_id) {
            $changedSettings[] = 'department';
        }
        if ((bool) $originalSettings['is_active'] !== $user->is_active) {
            $changedSettings[] = 'account status';
        }

        if ($changedSettings !== [] && ! $request->user()->is($user)) {
            $user->notify(new AccountSettingsUpdated($user, $changedSettings));
        }

        return back()->with('success', 'User account updated successfully.');
    }

    public function resetPassword(ResetManagedUserPasswordRequest $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        $user->update(['password' => Hash::make($request->validated('password'))]);

        $auditLogger->record($request->user(), 'user.password_reset', $user, "Reset the password for {$user->name}.", [], $request);

        if (! $request->user()->is($user)) {
            $user->notify(new PasswordResetByAdministrator);
        }

        return back()->with('success', 'Password reset successfully. Share the new password securely with the user.');
    }

    /** @param array{name: string, email: string, department_id: int|null, role: string, is_active: bool|string|int} $data */
    private function ensureCurrentAdminKeepsAccess(Request $request, User $user, array $data): void
    {
        if ($request->user()->is($user) && ($data['role'] !== 'admin' || ! $data['is_active'])) {
            throw ValidationException::withMessages(['role' => 'Use another active admin to change your own administrator access.']);
        }
    }

    private function ensureAnotherActiveAdminRemains(User $user, string $role, bool $isActive): void
    {
        $wouldRemoveAdministratorAccess = $user->role === 'admin' && $user->is_active && ($role !== 'admin' || ! $isActive);

        if ($wouldRemoveAdministratorAccess && User::query()->where('role', 'admin')->where('is_active', true)->whereKeyNot($user)->doesntExist()) {
            throw ValidationException::withMessages(['role' => 'At least one active administrator must remain in the system.']);
        }
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}
