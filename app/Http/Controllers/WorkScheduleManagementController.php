<?php

namespace App\Http\Controllers;

use App\AuditLogger;
use App\Http\Requests\StoreWorkScheduleRequest;
use App\Http\Requests\UpdateWorkScheduleRequest;
use App\Models\Employee;
use App\Models\WorkSchedule;
use App\Models\WorkScheduleAssignment;
use App\Notifications\WorkScheduleAssigned;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkScheduleManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.work-schedules', [
            'schedules' => WorkSchedule::query()
                ->withCount('employees')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.work-schedules-create');
    }

    public function show(Request $request, WorkSchedule $workSchedule): View
    {
        $this->authorizeAdmin($request);

        $workSchedule->loadCount('employees');

        return view('admin.work-schedules-show', [
            'workSchedule' => $workSchedule,
            'employees' => Employee::query()
                ->with(['department:id,name', 'workSchedule:id,name'])
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'work_schedule_id', 'department_id', 'first_name', 'last_name', 'employee_number']),
            'scheduledOverrides' => WorkScheduleAssignment::query()
                ->with(['employee.department:id,name', 'employee.workSchedule:id,name'])
                ->whereBelongsTo($workSchedule, 'workSchedule')
                ->orderByDesc('effective_date')
                ->get(),
        ]);
    }

    public function store(StoreWorkScheduleRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $schedule = WorkSchedule::create($this->scheduleData($request->validated()));

        $auditLogger->record($request->user(), 'work_schedule.created', $schedule, "Created the {$schedule->name} work schedule.", [], $request);

        return redirect()->route('admin.work-schedules.show', $schedule)
            ->with('success', 'Work schedule created. You can now assign employees to it.');
    }

    public function update(UpdateWorkScheduleRequest $request, WorkSchedule $workSchedule, AuditLogger $auditLogger): RedirectResponse
    {
        $workSchedule->update($this->scheduleData($request->validated()));

        $auditLogger->record($request->user(), 'work_schedule.updated', $workSchedule, "Updated the {$workSchedule->name} work schedule.", [], $request);

        return redirect()->route('admin.work-schedules.show', $workSchedule)
            ->with('success', 'Work schedule updated. Changes apply to future attendance actions.');
    }

    public function assign(Request $request, Employee $employee, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'work_schedule_id' => ['nullable', 'integer', 'exists:work_schedules,id'],
        ]);
        $schedule = isset($data['work_schedule_id']) ? WorkSchedule::find($data['work_schedule_id']) : null;
        $scheduleChanged = $employee->work_schedule_id !== $schedule?->id;

        if ($scheduleChanged) {
            $employee->update(['work_schedule_id' => $schedule?->id]);
            $employee->user?->notify(new WorkScheduleAssigned($schedule?->name));

            $auditLogger->record($request->user(), 'work_schedule.assigned', $employee, "Updated the default schedule for {$employee->first_name} {$employee->last_name}.", [
                'work_schedule_id' => $schedule?->id,
            ], $request);
        }

        return back()->with('success', $schedule ? "Assigned {$schedule->name} to {$employee->first_name}." : "Removed the schedule assignment for {$employee->first_name}.");
    }

    public function assignEmployees(Request $request, WorkSchedule $workSchedule, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'distinct', 'exists:employees,id'],
            'effective_date' => ['required', 'date'],
        ]);

        $employees = Employee::query()
            ->with('user')
            ->whereIn('id', $data['employee_ids'])
            ->get();
        $effectiveDate = Carbon::parse($data['effective_date'])->startOfDay();

        DB::transaction(function () use ($employees, $workSchedule, $effectiveDate, $request, $auditLogger): void {
            foreach ($employees as $employee) {
                $this->recordAssignment($employee, $workSchedule, $effectiveDate, $request, $auditLogger);
            }
        });

        return redirect()->route('admin.work-schedules.show', $workSchedule)
            ->with('success', "{$workSchedule->name} will apply to {$employees->count()} employee(s) from {$effectiveDate->format('M j, Y')}.");
    }

    public function revertAssignment(Request $request, WorkSchedule $workSchedule, WorkScheduleAssignment $workScheduleAssignment, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorizeAdmin($request);

        abort_unless($workScheduleAssignment->work_schedule_id === $workSchedule->id, 404);

        $workScheduleAssignment->load('employee.user');
        $effectiveDate = $workScheduleAssignment->effective_date->isFuture()
            ? $workScheduleAssignment->effective_date->copy()
            : today();

        $this->recordAssignment(
            $workScheduleAssignment->employee,
            null,
            $effectiveDate,
            $request,
            $auditLogger,
            true,
        );

        return redirect()->route('admin.work-schedules.show', $workSchedule)
            ->with('success', "{$workScheduleAssignment->employee->first_name} will return to their default schedule from {$effectiveDate->format('M j, Y')}.");
    }

    private function recordAssignment(Employee $employee, ?WorkSchedule $schedule, Carbon $effectiveDate, Request $request, AuditLogger $auditLogger, bool $returnsToDefault = false): void
    {
        $assignment = WorkScheduleAssignment::query()
            ->whereBelongsTo($employee)
            ->whereDate('effective_date', $effectiveDate)
            ->first();
        $assignmentWasCreated = $assignment === null;

        if ($assignmentWasCreated) {
            $assignment = new WorkScheduleAssignment([
                'employee_id' => $employee->id,
                'effective_date' => $effectiveDate,
            ]);
        }

        $assignment->fill([
            'work_schedule_id' => $schedule?->id,
            'assigned_by_user_id' => $request->user()->id,
        ]);
        $scheduleChanged = $assignment->isDirty('work_schedule_id');
        $assignment->save();

        if ($assignmentWasCreated || $scheduleChanged) {
            $employee->user?->notify(new WorkScheduleAssigned($schedule?->name, $effectiveDate->format('M j, Y'), $returnsToDefault));
        }

        $auditLogger->record($request->user(), $returnsToDefault ? 'work_schedule.reverted' : 'work_schedule.assigned', $employee, "Updated the schedule assignment for {$employee->first_name} {$employee->last_name}.", [
            'work_schedule_id' => $schedule?->id,
            'effective_date' => $effectiveDate->toDateString(),
        ], $request);
    }

    /** @param array{name: string, days: array<int, string>, time_in: string, time_out: string, overtime_start: string, overtime_minimum_minutes: int, overtime_maximum_minutes: int} $data */
    private function scheduleData(array $data): array
    {
        return [
            'name' => $data['name'],
            'days_json' => array_values($data['days']),
            'time_in' => $this->normalizeTime($data['time_in']),
            'time_out' => $this->normalizeTime($data['time_out']),
            'lunch_start' => '12:00:00',
            'lunch_end' => '13:00:00',
            'overtime_start' => $this->normalizeTime($data['overtime_start']),
            'overtime_minimum_minutes' => $data['overtime_minimum_minutes'],
            'overtime_maximum_minutes' => $data['overtime_maximum_minutes'],
        ];
    }

    private function normalizeTime(string $time): string
    {
        return strlen($time) === 5 ? $time.':00' : $time;
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}
