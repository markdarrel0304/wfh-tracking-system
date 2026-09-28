<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\WfhRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WfhReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $filters = $this->validatedFilters($request);
        $requestQuery = $this->filteredRequestQuery($filters);

        return view('reports.wfh', [
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'employees' => Employee::query()
                ->with('department:id,name')
                ->whereHas('wfhRequests', fn (Builder $query) => $this->applyRequestFilters($query, $filters))
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'department_id', 'first_name', 'last_name', 'employee_number']),
            'filters' => $filters,
            'selectedEmployee' => null,
            'summary' => $this->summaryFor(clone $requestQuery, $filters),
            'wfhRequests' => null,
        ]);
    }

    public function show(Request $request, Employee $employee): View
    {
        $this->authorizeAdmin($request);

        $filters = $this->validatedFilters($request);
        $filters['employee_id'] = $employee->id;
        $requestQuery = $this->filteredRequestQuery($filters);
        $summary = $this->summaryFor(clone $requestQuery, $filters);

        return view('reports.wfh', [
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'employees' => collect(),
            'filters' => $filters,
            'selectedEmployee' => $employee->load('department:id,name'),
            'summary' => $summary,
            'wfhRequests' => $requestQuery
                ->with(['employee.department', 'approver'])
                ->latest('date_from')
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeAdmin($request);

        $filters = $this->validatedFilters($request);
        $fileName = 'wfh-report-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Employee', 'Employee Number', 'Department', 'Arrangement', 'Requested Dates', 'Work Hours',
                'Status', 'Approved WFH Days', 'Submitted', 'Decision Date', 'Decision Note', 'Supporting Document',
            ]);

            $this->filteredRequestQuery($filters)
                ->with(['employee.department'])
                ->latest('date_from')
                ->latest('id')
                ->lazy(200)
                ->each(function (WfhRequest $wfhRequest) use ($output, $filters): void {
                    fputcsv($output, [
                        trim($wfhRequest->employee->first_name.' '.$wfhRequest->employee->last_name),
                        $wfhRequest->employee->employee_number,
                        $wfhRequest->employee->department?->name,
                        $wfhRequest->request_type,
                        $wfhRequest->date_from?->format('Y-m-d').' to '.$wfhRequest->date_to?->format('Y-m-d'),
                        $this->formatTime($wfhRequest->start_time).' – '.$this->formatTime($wfhRequest->end_time),
                        Str::headline($wfhRequest->status),
                        $wfhRequest->status === 'approved' ? $this->overlapDays($wfhRequest, $filters) : 0,
                        $wfhRequest->created_at?->format('Y-m-d H:i'),
                        $wfhRequest->approved_at?->format('Y-m-d H:i'),
                        $wfhRequest->remarks,
                        $wfhRequest->supporting_document ? 'Yes' : 'No',
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
            'status' => ['nullable', 'string', 'in:pending,approved,rejected,cancelled'],
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
     * @return Builder<WfhRequest>
     */
    private function filteredRequestQuery(array $filters): Builder
    {
        return $this->applyRequestFilters(WfhRequest::query(), $filters);
    }

    /**
     * @param  Builder<WfhRequest>  $query
     * @param  array{employee_id: int|null, department_id: int|null, status: string|null, from: string, to: string}  $filters
     * @return Builder<WfhRequest>
     */
    private function applyRequestFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['employee_id'], fn (Builder $query, int $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['department_id'], fn (Builder $query, int $departmentId) => $query->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('department_id', $departmentId)))
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->whereDate('date_from', '<=', $filters['to'])
            ->whereDate('date_to', '>=', $filters['from']);
    }

    /**
     * @param  Builder<WfhRequest>  $requestQuery
     * @param  array{employee_id: int|null, department_id: int|null, status: string|null, from: string, to: string}  $filters
     * @return array{requests: int, approved: int, pending: int, rejected: int, approved_days: int, guideline_weeks: int}
     */
    private function summaryFor(Builder $requestQuery, array $filters): array
    {
        $requests = 0;
        $approved = 0;
        $pending = 0;
        $rejected = 0;
        $approvedDays = 0;
        $weeklyDays = [];

        $requestQuery->lazyById(200)->each(function (WfhRequest $wfhRequest) use (&$requests, &$approved, &$pending, &$rejected, &$approvedDays, &$weeklyDays, $filters): void {
            $requests++;
            $approved += $wfhRequest->status === 'approved' ? 1 : 0;
            $pending += $wfhRequest->status === 'pending' ? 1 : 0;
            $rejected += $wfhRequest->status === 'rejected' ? 1 : 0;

            if ($wfhRequest->status !== 'approved') {
                return;
            }

            $approvedDays += $this->overlapDays($wfhRequest, $filters);

            foreach ($this->overlapDates($wfhRequest, $filters) as $date) {
                $weekKey = $wfhRequest->employee_id.'-'.$date->isoWeekYear.'-'.$date->isoWeek;
                $weeklyDays[$weekKey][$date->toDateString()] = true;
            }
        });

        $guidelineWeeks = collect($weeklyDays)
            ->filter(fn (array $days): bool => count($days) > 3)
            ->count();

        return [
            'requests' => $requests,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'approved_days' => $approvedDays,
            'guideline_weeks' => $guidelineWeeks,
        ];
    }

    /**
     * @param  array{from: string, to: string}  $filters
     */
    private function overlapDays(WfhRequest $wfhRequest, array $filters): int
    {
        return count($this->overlapDates($wfhRequest, $filters));
    }

    /**
     * @param  array{from: string, to: string}  $filters
     * @return array<int, Carbon>
     */
    private function overlapDates(WfhRequest $wfhRequest, array $filters): array
    {
        $start = $wfhRequest->date_from->greaterThan($filters['from']) ? $wfhRequest->date_from->copy() : Carbon::parse($filters['from']);
        $end = $wfhRequest->date_to->lessThan($filters['to']) ? $wfhRequest->date_to->copy() : Carbon::parse($filters['to']);
        $dates = [];

        while ($start->lessThanOrEqualTo($end)) {
            $dates[] = $start->copy();
            $start->addDay();
        }

        return $dates;
    }

    private function formatTime(?string $time): ?string
    {
        return $time ? Carbon::parse($time)->format('g:i A') : null;
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}
