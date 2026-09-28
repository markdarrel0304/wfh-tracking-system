<?php

namespace App\Http\Controllers;

use App\Models\AccomplishmentReport;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccomplishmentReportSummaryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $filters = $this->validatedFilters($request);
        $reportQuery = $this->filteredReportQuery($filters);

        return view('reports.accomplishments', [
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'employees' => Employee::query()
                ->with('department:id,name')
                ->whereHas('accomplishmentReports', fn (Builder $query) => $this->applyReportFilters($query, $filters))
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'department_id', 'first_name', 'last_name', 'employee_number']),
            'filters' => $filters,
            'selectedEmployee' => null,
            'summary' => $this->summaryFor(clone $reportQuery, $filters),
            'reports' => null,
        ]);
    }

    public function show(Request $request, Employee $employee): View
    {
        $this->authorizeAdmin($request);

        $filters = $this->validatedFilters($request);
        $filters['employee_id'] = $employee->id;
        $reportQuery = $this->filteredReportQuery($filters);
        $summary = $this->summaryFor(clone $reportQuery, $filters);
        $reports = $reportQuery
            ->with(['outputAttachments.dailyTask', 'reviewer'])
            ->latest('date')
            ->paginate(20)
            ->withQueryString();

        return view('reports.accomplishments', [
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'employees' => collect(),
            'filters' => $filters,
            'selectedEmployee' => $employee->load('department:id,name'),
            'summary' => $summary,
            'reports' => $reports,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeAdmin($request);

        $filters = $this->validatedFilters($request);
        $fileName = 'accomplishments-report-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Employee', 'Employee Number', 'Department', 'Workday', 'Report Status', 'Summary', 'Blockers',
                'Next Steps', 'Accomplishment Title', 'Category', 'Progress Status', 'Submitted At', 'Reviewed At', 'Reviewer Note', 'Output Files',
            ]);

            $this->filteredReportQuery($filters)
                ->with(['employee.department', 'outputAttachments'])
                ->latest('date')
                ->lazy(200)
                ->each(function (AccomplishmentReport $report) use ($output): void {
                    fputcsv($output, [
                        trim($report->employee->first_name.' '.$report->employee->last_name),
                        $report->employee->employee_number,
                        $report->employee->department?->name,
                        $report->date?->format('Y-m-d'),
                        $this->reportStatus($report),
                        $report->summary,
                        $report->blockers,
                        $report->next_steps,
                        $report->title ?? $report->dailyTask?->title,
                        $report->category,
                        $report->progress_status,
                        $report->submitted_at?->format('Y-m-d H:i'),
                        $report->reviewed_at?->format('Y-m-d H:i'),
                        $report->review_note,
                        $report->outputAttachments->count(),
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
            'status' => ['nullable', 'string', 'in:submitted,reviewed,revision'],
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
     * @return Builder<AccomplishmentReport>
     */
    private function filteredReportQuery(array $filters): Builder
    {
        return $this->applyReportFilters(AccomplishmentReport::query(), $filters);
    }

    /**
     * @param  Builder<AccomplishmentReport>  $query
     * @param  array{employee_id: int|null, department_id: int|null, status: string|null, from: string, to: string}  $filters
     * @return Builder<AccomplishmentReport>
     */
    private function applyReportFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->whereNotNull('submitted_at')
            ->when($filters['employee_id'], fn (Builder $query, int $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['department_id'], fn (Builder $query, int $departmentId) => $query->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('department_id', $departmentId)))
            ->when($filters['status'] === 'revision', fn (Builder $query) => $query->whereNotNull('revision_requested_at'))
            ->when($filters['status'] === 'submitted', fn (Builder $query) => $query->where('status', 'submitted')->whereNull('revision_requested_at'))
            ->when($filters['status'] === 'reviewed', fn (Builder $query) => $query->where('status', 'reviewed'))
            ->whereBetween('date', [$filters['from'], $filters['to']]);
    }

    /**
     * @param  Builder<AccomplishmentReport>  $reportQuery
     * @param  array{employee_id: int|null, department_id: int|null, status: string|null, from: string, to: string}  $filters
     * @return array{reports: int, reviewed: int, pending: int, revisions: int, attachments: int}
     */
    private function summaryFor(Builder $reportQuery, array $filters): array
    {
        $reports = 0;
        $reviewed = 0;
        $pending = 0;
        $revisions = 0;
        $attachments = 0;

        $reportQuery->withCount('outputAttachments')->lazyById(200)->each(function (AccomplishmentReport $report) use (&$reports, &$reviewed, &$pending, &$revisions, &$attachments): void {
            $reports++;
            $reviewed += $report->status === 'reviewed' ? 1 : 0;
            $revisions += $report->revision_requested_at !== null ? 1 : 0;
            $pending += $report->status === 'submitted' && $report->revision_requested_at === null ? 1 : 0;
            $attachments += $report->output_attachments_count;
        });

        return [
            'reports' => $reports,
            'reviewed' => $reviewed,
            'pending' => $pending,
            'revisions' => $revisions,
            'attachments' => $attachments,
        ];
    }

    private function reportStatus(AccomplishmentReport $report): string
    {
        if ($report->revision_requested_at) {
            return 'Revision requested';
        }

        return Str::headline($report->status);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}
