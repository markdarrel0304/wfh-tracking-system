<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $filters = $this->validatedFilters($request);
        $attendanceQuery = $this->filteredAttendanceQuery($filters);

        return view('reports.attendance', [
            'attendances' => null,
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'employees' => Employee::query()
                ->with('department:id,name')
                ->whereHas('attendance', fn (Builder $query) => $this->applyAttendanceFilters($query, $filters))
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'department_id', 'first_name', 'last_name', 'employee_number']),
            'filters' => $filters,
            'selectedEmployee' => null,
            'summary' => $this->summaryFor(clone $attendanceQuery),
        ]);
    }

    public function show(Request $request, Employee $employee): View
    {
        $this->authorizeAdmin($request);

        $filters = $this->validatedFilters($request);
        $filters['employee_id'] = $employee->id;
        $attendanceQuery = $this->filteredAttendanceQuery($filters);
        $summary = $this->summaryFor(clone $attendanceQuery);

        return view('reports.attendance', [
            'attendances' => $attendanceQuery
                ->with(['employee.department', 'wfhRequest'])
                ->latest('date')
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'employees' => collect(),
            'filters' => $filters,
            'selectedEmployee' => $employee->load('department:id,name'),
            'summary' => $summary,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeAdmin($request);

        $filters = $this->validatedFilters($request);
        $fileName = 'attendance-report-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'Date', 'Employee', 'Employee Number', 'Department', 'Status', 'Time In',
                'Regular Time Out', 'Overtime Start', 'Overtime End', 'Regular Worked',
                'Overtime', 'Paid Work Time', 'Work Arrangement', 'Early Departure',
            ]);

            $this->filteredAttendanceQuery($filters)
                ->with(['employee.department', 'wfhRequest'])
                ->latest('date')
                ->latest('id')
                ->lazy(200)
                ->each(function (Attendance $attendance) use ($output): void {
                    fputcsv($output, [
                        $attendance->date?->format('Y-m-d'),
                        trim($attendance->employee->first_name.' '.$attendance->employee->last_name),
                        $attendance->employee->employee_number,
                        $attendance->employee->department?->name,
                        Str::headline($attendance->status),
                        $attendance->formattedTimeIn(),
                        $attendance->formattedTimeOut(),
                        $attendance->formattedOvertimeIn(),
                        $attendance->formattedOvertimeOut(),
                        $this->duration($attendance->regularWorkedMinutes()),
                        $this->duration($attendance->overtimeMinutes()),
                        $attendance->workedDuration(),
                        $attendance->wfhRequest ? 'Work From Home' : 'Office / on-site',
                        $attendance->early_out ? 'Yes' : 'No',
                    ]);
                });

            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array{employee_id: int|null, department_id: int|null, status: string|null, from: string, to: string}
     */
    private function validatedFilters(Request $request): array
    {
        $filters = $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'status' => ['nullable', 'string', 'in:present,late,absent,on-leave'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            'employee_id' => isset($filters['employee_id']) ? (int) $filters['employee_id'] : null,
            'department_id' => isset($filters['department_id']) ? (int) $filters['department_id'] : null,
            'status' => $filters['status'] ?? null,
            'from' => $filters['from'] ?? now()->startOfMonth()->toDateString(),
            'to' => $filters['to'] ?? now()->toDateString(),
        ];
    }

    /**
     * @param  array{employee_id: int|null, department_id: int|null, status: string|null, from: string, to: string}  $filters
     * @return Builder<Attendance>
     */
    private function filteredAttendanceQuery(array $filters): Builder
    {
        return $this->applyAttendanceFilters(Attendance::query(), $filters);
    }

    /**
     * @param  Builder<Attendance>  $query
     * @param  array{employee_id: int|null, department_id: int|null, status: string|null, from: string, to: string}  $filters
     * @return Builder<Attendance>
     */
    private function applyAttendanceFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['employee_id'], fn (Builder $query, int $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['department_id'], fn (Builder $query, int $departmentId) => $query->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('department_id', $departmentId)))
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->whereBetween('date', [$filters['from'], $filters['to']]);
    }

    /**
     * @return array{records: int, completed: int, late: int, early_departures: int, regular_minutes: int, overtime_minutes: int, paid_minutes: int}
     */
    private function summaryFor(Builder $attendanceQuery): array
    {
        $records = 0;
        $completed = 0;
        $late = 0;
        $earlyDepartures = 0;
        $regularMinutes = 0;
        $overtimeMinutes = 0;

        $attendanceQuery->lazyById(200)->each(function (Attendance $attendance) use (&$records, &$completed, &$late, &$earlyDepartures, &$regularMinutes, &$overtimeMinutes): void {
            $records++;
            $completed += $attendance->time_out !== null ? 1 : 0;
            $late += $attendance->status === 'late' ? 1 : 0;
            $earlyDepartures += $attendance->early_out ? 1 : 0;
            $regularMinutes += $attendance->regularWorkedMinutes() ?? 0;
            $overtimeMinutes += $attendance->overtimeMinutes() ?? 0;
        });

        return [
            'records' => $records,
            'completed' => $completed,
            'late' => $late,
            'early_departures' => $earlyDepartures,
            'regular_minutes' => $regularMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'paid_minutes' => $regularMinutes + $overtimeMinutes,
        ];
    }

    private function duration(?int $minutes): ?string
    {
        if ($minutes === null) {
            return null;
        }

        return sprintf('%dh %02dm', intdiv($minutes, 60), $minutes % 60);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}
