<?php

namespace App\Http\Controllers;

use App\AuditLogger;
use App\Http\Requests\RequestAccomplishmentReportRevisionRequest;
use App\Http\Requests\ReviewAccomplishmentReportRequest;
use App\Http\Requests\StoreAccomplishmentReportRequest;
use App\Models\AccomplishmentReport;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\AccomplishmentReportReviewed;
use App\Notifications\AccomplishmentReportRevisionRequested;
use App\Notifications\AccomplishmentReportSubmitted;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AccomplishmentReportController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $this->employeeForCurrentUser();
        Gate::authorize('viewAny', AccomplishmentReport::class);

        $selectedDate = $request->filled('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : today();
        $dayReports = $employee->accomplishmentReports()
            ->with(['dailyTask', 'outputAttachments'])
            ->whereDate('date', $selectedDate)
            ->latest()
            ->get();
        $editingReport = null;

        if ($request->filled('edit')) {
            $editingReport = $employee->accomplishmentReports()
                ->with(['dailyTask', 'outputAttachments'])
                ->whereDate('date', $selectedDate)
                ->findOrFail($request->integer('edit'));

            Gate::authorize('update', $editingReport);
        }
        $calendarMonth = $selectedDate->copy()->startOfMonth();

        return view('accomplishments.reports', [
            'selectedDate' => $selectedDate,
            'dayReports' => $dayReports,
            'editingReport' => $editingReport,
            'calendarMonth' => $calendarMonth,
            'calendarDays' => $this->calendarDaysFor($calendarMonth, $selectedDate),
            'calendarYears' => range($selectedDate->year - 1, $selectedDate->year + 1),
            'calendarMonths' => $this->calendarMonths(),
        ]);
    }

    public function store(StoreAccomplishmentReportRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $employee = $this->employeeForCurrentUser();
        Gate::authorize('create', AccomplishmentReport::class);

        $data = $request->validated();
        $files = $request->file('attachments', []);
        $isSubmitting = ($data['submission_action'] ?? 'submit') === 'submit';

        $savedReport = DB::transaction(function () use ($employee, $data, $files, $isSubmitting): AccomplishmentReport {
            $task = null;
            if (isset($data['daily_task_id'])) {
                $task = $employee->dailyTasks()
                    ->lockForUpdate()
                    ->findOrFail($data['daily_task_id']);

                if (! $task->date->isSameDay(Carbon::parse($data['date'])) || $task->status !== 'done') {
                    abort(422, 'Complete the selected task before submitting its accomplishment.');
                }
            }

            $report = isset($data['accomplishment_report_id'])
                ? $employee->accomplishmentReports()->lockForUpdate()->findOrFail($data['accomplishment_report_id'])
                : ($task ? AccomplishmentReport::query()->where('daily_task_id', $task->id)->first() : null);

            if ($report) {
                Gate::authorize('update', $report);
            }
            $reportData = [
                'date' => $data['date'],
                'title' => $data['title'] ?? $task?->title,
                'category' => $data['category'] ?? null,
                'progress_status' => $data['progress_status'] ?? 'completed',
                'summary' => $data['summary'],
                'blockers' => $data['blockers'] ?? null,
                'next_steps' => $data['next_steps'] ?? null,
                'status' => 'submitted',
                'submitted_at' => $isSubmitting ? now() : null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_note' => null,
                'revision_requested_at' => null,
            ];

            if ($report) {
                $report->update($reportData);
            } else {
                $report = AccomplishmentReport::query()->create([
                    ...$reportData,
                    'employee_id' => $employee->id,
                    'daily_task_id' => $task?->id,
                ]);
            }

            foreach ($files as $file) {
                $fileName = basename(str_replace('\\', '/', $file->getClientOriginalName()));
                $path = $file->store('output_attachments/'.$report->id, 'local');

                $report->outputAttachments()->create([
                    'daily_task_id' => $task?->id,
                    'file_path' => $path,
                    'file_name' => $fileName,
                    'mime_type' => $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                    'uploaded_at' => now(),
                ]);
            }

            return $report;
        });

        if ($isSubmitting) {
            $auditLogger->record(
                $request->user(),
                'accomplishment_report.submitted',
                $savedReport,
                'Submitted a daily accomplishment report.',
                [
                    'employee' => $employee->first_name.' '.$employee->last_name,
                    'workday' => $savedReport->date->toDateString(),
                    'summary' => $savedReport->summary,
                    'blockers' => $savedReport->blockers,
                    'next_steps' => $savedReport->next_steps,
                    'title' => $savedReport->title ?? $savedReport->dailyTask?->title,
                    'attachments_added' => count($files),
                ],
                $request,
            );

            $savedReport->load(['employee', 'dailyTask']);
            User::query()
                ->whereIn('role', ['admin', 'supervisor'])
                ->where('is_active', true)
                ->each(fn (User $user): mixed => $user->notify(new AccomplishmentReportSubmitted($savedReport)));
        }

        return redirect()->to(route('accomplishments.reports', ['date' => $data['date']]).'#history')
            ->with('report_success', $isSubmitting
                ? 'Your accomplishment was submitted for review with '.count($files).' attachment(s).'
                : 'Your accomplishment draft was saved. You can submit it for review when it is ready.');
    }

    public function approval(): View
    {
        Gate::authorize('viewAny', AccomplishmentReport::class);

        abort_unless(in_array(auth()->user()->role, ['admin', 'supervisor'], true), 403);

        $pendingReports = AccomplishmentReport::query()
            ->with(['employee.user', 'employee.department', 'dailyTask', 'outputAttachments.dailyTask'])
            ->where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->oldest('submitted_at')
            ->get();
        $recentReviews = AccomplishmentReport::query()
            ->with(['employee.user', 'reviewer.user', 'dailyTask', 'outputAttachments.dailyTask'])
            ->where('status', 'reviewed')
            ->latest('reviewed_at')
            ->limit(8)
            ->get();
        $reviewedCount = AccomplishmentReport::query()
            ->where('status', 'reviewed')
            ->count();
        $this->addTaskProgressTo($pendingReports);

        return view('approvals.accomplishment-reports', compact('pendingReports', 'recentReviews', 'reviewedCount'));
    }

    public function updateApproval(ReviewAccomplishmentReportRequest $request, AccomplishmentReport $accomplishmentReport, AuditLogger $auditLogger): RedirectResponse
    {
        Gate::authorize('approve', $accomplishmentReport);

        $approver = $this->employeeForCurrentUser();
        $reviewData = $request->validated();

        DB::transaction(function () use ($accomplishmentReport, $approver, $reviewData): void {
            $report = AccomplishmentReport::query()
                ->lockForUpdate()
                ->findOrFail($accomplishmentReport->id);

            if ($report->status !== 'submitted' || $report->submitted_at === null) {
                abort(409, 'This accomplishment report has already been reviewed.');
            }

            $report->update([
                'status' => 'reviewed',
                'reviewed_by' => $approver->id,
                'reviewed_at' => now(),
                'review_note' => $reviewData['review_note'] ?? null,
                'revision_requested_at' => null,
            ]);
        });

        $reviewedReport = $accomplishmentReport->fresh(['employee.user', 'dailyTask']);

        $auditLogger->record(
            $request->user(),
            'accomplishment_report.reviewed',
            $reviewedReport,
            'Reviewed an accomplishment report.',
            [
                'employee' => $reviewedReport->employee->first_name.' '.$reviewedReport->employee->last_name,
                'workday' => $reviewedReport->date->toDateString(),
                'review_note' => $reviewedReport->review_note,
            ],
            $request,
        );

        $reviewedReport->employee->user?->notify(new AccomplishmentReportReviewed($reviewedReport));

        return redirect()->route('approvals.accomplishment-reports')
            ->with('approval_success', 'Accomplishment report marked as reviewed.');
    }

    public function requestRevision(RequestAccomplishmentReportRevisionRequest $request, AccomplishmentReport $accomplishmentReport, AuditLogger $auditLogger): RedirectResponse
    {
        Gate::authorize('approve', $accomplishmentReport);

        DB::transaction(function () use ($accomplishmentReport, $request): void {
            $report = AccomplishmentReport::query()
                ->lockForUpdate()
                ->findOrFail($accomplishmentReport->id);

            if ($report->status !== 'submitted' || $report->submitted_at === null) {
                abort(409, 'This accomplishment report has already been reviewed.');
            }

            $report->update([
                'review_note' => $request->validated('review_note'),
                'revision_requested_at' => now(),
            ]);
        });

        $revisedReport = $accomplishmentReport->fresh(['employee.user', 'dailyTask']);

        $auditLogger->record(
            $request->user(),
            'accomplishment_report.revision_requested',
            $revisedReport,
            'Requested a revision for an accomplishment report.',
            [
                'employee' => $revisedReport->employee->first_name.' '.$revisedReport->employee->last_name,
                'workday' => $revisedReport->date->toDateString(),
                'review_note' => $revisedReport->review_note,
            ],
            $request,
        );

        $revisedReport->employee->user?->notify(new AccomplishmentReportRevisionRequested($revisedReport));

        return redirect()->route('approvals.accomplishment-reports')
            ->with('approval_success', 'Revision requested from the employee.');
    }

    private function employeeForCurrentUser(): Employee
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            abort(404, 'Employee profile not found.');
        }

        return $employee;
    }

    /**
     * @return array<int, array{day: int, date: string, isCurrentMonth: bool, isSelected: bool, isToday: bool}>
     */
    private function calendarDaysFor(Carbon $calendarMonth, Carbon $selectedDate): array
    {
        $firstVisibleDate = $calendarMonth->copy()->startOfMonth()->subDays($calendarMonth->dayOfWeek);
        $today = today();
        $calendarDays = [];

        for ($index = 0; $index < 42; $index++) {
            $date = $firstVisibleDate->copy()->addDays($index);
            $calendarDays[] = [
                'day' => $date->day,
                'date' => $date->toDateString(),
                'isCurrentMonth' => $date->month === $calendarMonth->month,
                'isSelected' => $date->isSameDay($selectedDate),
                'isToday' => $date->isSameDay($today),
            ];
        }

        return $calendarDays;
    }

    /**
     * @return array<int, string>
     */
    private function calendarMonths(): array
    {
        return [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
            7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
    }

    /**
     * @param  Collection<int, AccomplishmentReport>  $reports
     */
    private function addTaskProgressTo(Collection $reports): void
    {
        if ($reports->isEmpty()) {
            return;
        }

        $tasks = DailyTask::query()
            ->whereIn('employee_id', $reports->pluck('employee_id'))
            ->whereIn('date', $reports->pluck('date')->map->toDateString())
            ->get()
            ->groupBy(fn (DailyTask $task) => $task->employee_id.'|'.$task->date->toDateString());

        $reports->each(function (AccomplishmentReport $report) use ($tasks): void {
            if ($report->daily_task_id) {
                $report->setAttribute('planned_tasks_count', 1);
                $report->setAttribute('completed_tasks_count', $report->dailyTask?->status === 'done' ? 1 : 0);

                return;
            }

            $reportTasks = $tasks->get($report->employee_id.'|'.$report->date->toDateString(), collect());
            $report->setAttribute('planned_tasks_count', $reportTasks->count());
            $report->setAttribute('completed_tasks_count', $reportTasks->where('status', 'done')->count());
        });
    }
}
