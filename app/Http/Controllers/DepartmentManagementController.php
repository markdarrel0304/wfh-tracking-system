<?php

namespace App\Http\Controllers;

use App\AuditLogger;
use App\Http\Requests\AssignEmployeeToDepartmentRequest;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Notifications\DepartmentAssignmentChanged;
use App\Notifications\DepartmentUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DepartmentManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $departments = Department::query()
            ->withCount('employees')
            ->with(['employees' => fn ($query) => $query->with('user:id,name')->orderBy('first_name')->orderBy('last_name')])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($departmentQuery) use ($search): void {
                    $departmentQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $employees = Employee::query()
            ->with(['department:id,name,code'])
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'user_id', 'employee_number', 'first_name', 'last_name', 'department_id', 'status']);

        return view('admin.departments', compact('departments', 'employees', 'filters'));
    }

    public function store(StoreDepartmentRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $department = Department::create($this->departmentData($request->validated()));

        $auditLogger->record($request->user(), 'department.created', $department, "Created the {$department->name} department.", [
            'code' => $department->code,
            'location' => $department->location,
        ], $request);

        return back()->with('success', 'Department created successfully.');
    }

    public function update(UpdateDepartmentRequest $request, Department $department, AuditLogger $auditLogger): RedirectResponse
    {
        $department->update($this->departmentData($request->validated()));

        $auditLogger->record($request->user(), 'department.updated', $department, "Updated the {$department->name} department.", [
            'code' => $department->code,
            'location' => $department->location,
            'is_active' => $department->is_active,
        ], $request);

        if ($department->wasChanged(['name', 'code', 'location', 'department_head', 'is_active'])) {
            $department->employees()
                ->with('user')
                ->get()
                ->each(fn (Employee $employee): mixed => $employee->user?->notify(new DepartmentUpdated($department)));
        }

        return back()->with('success', 'Department updated successfully.');
    }

    public function assignEmployee(AssignEmployeeToDepartmentRequest $request, Department $department, AuditLogger $auditLogger): RedirectResponse
    {
        if (! $department->is_active) {
            throw ValidationException::withMessages(['employee_id' => 'Activate this department before assigning employees to it.']);
        }

        $employee = Employee::query()
            ->with(['department', 'user'])
            ->findOrFail($request->validated('employee_id'));
        $previousDepartmentName = $employee->department?->name;

        DB::transaction(function () use ($department, $employee): void {
            $employee->update(['department_id' => $department->id]);
            $employee->user?->update(['department_id' => $department->id]);
        });

        $auditLogger->record(
            $request->user(),
            'department.employee_assigned',
            $department,
            "Assigned {$employee->first_name} {$employee->last_name} to the {$department->name} department.",
            [
                'employee_id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'previous_department' => $previousDepartmentName,
            ],
            $request,
        );

        $employee->user?->notify(new DepartmentAssignmentChanged($department, $previousDepartmentName));

        return back()->with('success', "{$employee->first_name} {$employee->last_name} is now assigned to {$department->name}.");
    }

    /** @param array{name: string, code: string, location: string, department_head: string|null, department_head_count: int|string, is_active: bool|string|int} $data */
    private function departmentData(array $data): array
    {
        return [
            'name' => $data['name'],
            'code' => Str::upper(trim($data['code'])),
            'location' => trim($data['location']),
            'department_head' => $data['department_head'] ? trim($data['department_head']) : null,
            'department_head_count' => (int) $data['department_head_count'],
            'is_active' => (bool) $data['is_active'],
        ];
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}
