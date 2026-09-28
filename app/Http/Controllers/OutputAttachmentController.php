<?php

namespace App\Http\Controllers;

use App\AuditLogger;
use App\Http\Requests\StoreOutputAttachmentRequest;
use App\Models\AccomplishmentReport;
use App\Models\DailyTask;
use App\Models\Employee;
use App\Models\OutputAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OutputAttachmentController extends Controller
{
    public function index(): View
    {
        $employee = $this->employeeForCurrentUser();
        Gate::authorize('viewAny', OutputAttachment::class);

        $reports = $employee->accomplishmentReports()
            ->with('outputAttachments.dailyTask')
            ->latest('date')
            ->limit(12)
            ->get();
        $attachmentCount = $reports->sum(fn (AccomplishmentReport $report) => $report->outputAttachments->count());
        $reportCountWithAttachments = $reports->filter(fn (AccomplishmentReport $report) => $report->outputAttachments->isNotEmpty())->count();

        $tasksByDate = $employee->dailyTasks()
            ->whereIn('date', $reports->pluck('date')->map->toDateString())
            ->orderBy('due_time')
            ->get()
            ->groupBy(fn (DailyTask $task) => $task->date->toDateString());

        return view('accomplishments.attachments', compact('reports', 'tasksByDate', 'attachmentCount', 'reportCountWithAttachments'));
    }

    public function store(StoreOutputAttachmentRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $employee = $this->employeeForCurrentUser();
        Gate::authorize('create', OutputAttachment::class);

        $report = $employee->accomplishmentReports()
            ->findOrFail($request->integer('accomplishment_report_id'));
        Gate::authorize('update', $report);

        $dailyTask = null;

        if ($request->filled('daily_task_id')) {
            $dailyTask = $employee->dailyTasks()->findOrFail($request->integer('daily_task_id'));
        }

        foreach ($request->file('attachments') as $file) {
            $fileName = basename(str_replace('\\', '/', $file->getClientOriginalName()));
            $path = $file->store('output_attachments/'.$report->id, 'local');

            $outputAttachment = $report->outputAttachments()->create([
                'daily_task_id' => $dailyTask?->id,
                'file_path' => $path,
                'file_name' => $fileName,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'uploaded_at' => now(),
            ]);

            $auditLogger->record(
                $request->user(),
                'output_attachment.uploaded',
                $outputAttachment,
                'Uploaded an output attachment.',
                [
                    'employee' => $employee->first_name.' '.$employee->last_name,
                    'report_date' => $report->date->toDateString(),
                    'file_name' => $outputAttachment->file_name,
                    'file_type' => $outputAttachment->mime_type,
                    'file_size_bytes' => $outputAttachment->size_bytes,
                    'task' => $dailyTask?->title,
                ],
                $request,
            );
        }

        return redirect()->route('accomplishments.attachments')
            ->with('attachment_success', 'Your output attachment was uploaded successfully.');
    }

    public function download(OutputAttachment $outputAttachment): StreamedResponse
    {
        Gate::authorize('view', $outputAttachment);

        abort_unless(Storage::disk('local')->exists($outputAttachment->file_path), 404);

        return Storage::disk('local')->download($outputAttachment->file_path, $outputAttachment->file_name);
    }

    public function destroy(OutputAttachment $outputAttachment, AuditLogger $auditLogger): RedirectResponse
    {
        Gate::authorize('delete', $outputAttachment);

        $auditLogger->record(
            request()->user(),
            'output_attachment.deleted',
            $outputAttachment,
            'Deleted an output attachment.',
            [
                'file_name' => $outputAttachment->file_name,
                'file_type' => $outputAttachment->mime_type,
                'file_size_bytes' => $outputAttachment->size_bytes,
                'report_id' => $outputAttachment->accomplishment_report_id,
                'task_id' => $outputAttachment->daily_task_id,
            ],
        );

        Storage::disk('local')->delete($outputAttachment->file_path);
        $outputAttachment->delete();

        return redirect()->route('accomplishments.attachments')
            ->with('attachment_success', 'Output attachment removed.');
    }

    private function employeeForCurrentUser(): Employee
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            abort(404, 'Employee profile not found.');
        }

        return $employee;
    }
}
