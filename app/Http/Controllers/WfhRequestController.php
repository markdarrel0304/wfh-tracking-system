<?php

namespace App\Http\Controllers;

use App\AuditLogger;
use App\Models\User;
use App\Models\WfhRequest;
use App\Notifications\WfhRequestReviewed;
use App\Notifications\WfhRequestSubmitted;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WfhRequestController extends Controller
{
    public function index(): View
    {
        $employee = auth()->user()->employee;
        if (! $employee) {
            abort(404, 'Employee profile not found');
        }

        $requests = $employee->wfhRequests()->orderBy('created_at', 'desc')->get();

        $total = $requests->count();
        $approved = $requests->where('status', 'approved')->count();
        $pending = $requests->where('status', 'pending')->count();
        $rejected = $requests->where('status', 'rejected')->count();

        return view('wfh.my-requests', compact('requests', 'total', 'approved', 'pending', 'rejected'));
    }

    public function calendar(Request $request): View
    {
        $employee = auth()->user()->employee;
        if (! $employee) {
            abort(404, 'Employee profile not found');
        }

        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);
        $calendarMonth = Carbon::create($year, $month, 1);

        $requests = $employee->wfhRequests()
            ->whereDate('date_from', '<=', $calendarMonth->copy()->endOfMonth())
            ->whereDate('date_to', '>=', $calendarMonth->copy()->startOfMonth())
            ->orderBy('date_from')
            ->get();

        return view('wfh.calendar', compact('calendarMonth', 'requests'));
    }

    public function create(): View
    {
        Gate::authorize('create', WfhRequest::class);

        $employee = auth()->user()->employee;
        if (! $employee) {
            abort(404, 'Employee profile not found');
        }

        $year = request()->get('year', Carbon::now()->year);
        $month = request()->get('month', Carbon::now()->month);

        $carbon = Carbon::create($year, $month, 1);
        $daysInMonth = $carbon->daysInMonth;
        $firstDay = $carbon->dayOfWeekIso;
        $today = Carbon::now();

        $calendarDays = [];
        $selectedDate = request()->get('date', Carbon::now()->format('Y-m-d'));

        // Previous month days
        $prevMonth = $carbon->copy()->subMonth();
        for ($i = $firstDay === 7 ? 0 : $firstDay - 1; $i > 0; $i--) {
            $calendarDays[] = [
                'day' => $prevMonth->daysInMonth - $i + 1,
                'date' => $prevMonth->copy()->day($prevMonth->daysInMonth - $i + 1)->format('Y-m-d'),
                'isCurrentMonth' => false,
                'isSelected' => false,
                'isToday' => false,
            ];
        }

        // Current month days
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dateStr = $date->format('Y-m-d');
            $calendarDays[] = [
                'day' => $day,
                'date' => $dateStr,
                'isCurrentMonth' => true,
                'isSelected' => $dateStr === $selectedDate,
                'isToday' => $date->isSameDay($today),
            ];
        }

        // Next month days
        $totalCells = ($firstDay === 7 ? 0 : $firstDay - 1) + $daysInMonth;
        $remainingDays = 42 - $totalCells;
        for ($day = 1; $day <= $remainingDays; $day++) {
            $calendarDays[] = [
                'day' => $day,
                'date' => $carbon->copy()->addMonth()->day($day)->format('Y-m-d'),
                'isCurrentMonth' => false,
                'isSelected' => false,
                'isToday' => false,
            ];
        }

        return view('wfh.create-request', compact('employee', 'year', 'month', 'calendarDays'));
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        Gate::authorize('create', WfhRequest::class);

        $employee = auth()->user()->employee;
        if (! $employee) {
            abort(404, 'Employee profile not found');
        }

        $data = $request->validate([
            'request_type' => ['required', 'string', 'max:255'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'reason' => ['required', 'string', 'max:500'],
            'supporting_document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:2048'],
        ]);

        $hasOverlappingRequest = $employee->wfhRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('date_from', '<=', $data['date_to'])
            ->whereDate('date_to', '>=', $data['date_from'])
            ->exists();

        if ($hasOverlappingRequest) {
            return back()
                ->withInput()
                ->withErrors(['date_from' => 'You already have a pending or approved WFH request that overlaps these dates.']);
        }

        unset($data['supporting_document']);

        // Format time if provided
        if ($request->filled('start_time')) {
            $data['start_time'] = $request->start_time.':00';
        }
        if ($request->filled('end_time')) {
            $data['end_time'] = $request->end_time.':00';
        }

        if ($request->hasFile('supporting_document')) {
            $file = $request->file('supporting_document');
            $path = $file->store('wfh_documents', 'local');
            $data['supporting_document'] = $path;
        }

        $data['employee_id'] = $employee->id;
        $data['status'] = 'pending';

        $wfhRequest = WfhRequest::create($data);
        $wfhRequest->load('employee');

        $auditLogger->record(
            $request->user(),
            'wfh_request.submitted',
            $wfhRequest,
            'Submitted a Work From Home request.',
            [
                'employee' => $employee->first_name.' '.$employee->last_name,
                'arrangement' => $wfhRequest->request_type,
                'date_from' => $wfhRequest->date_from->toDateString(),
                'date_to' => $wfhRequest->date_to->toDateString(),
                'start_time' => $wfhRequest->start_time,
                'end_time' => $wfhRequest->end_time,
                'reason' => $wfhRequest->reason,
                'supporting_document_uploaded' => $wfhRequest->supporting_document !== null,
            ],
            $request,
        );

        User::query()
            ->whereIn('role', ['admin', 'supervisor'])
            ->each(fn (User $user) => $user->notify(new WfhRequestSubmitted($wfhRequest)));

        return redirect()->route('wfh.my-requests')
            ->with('success', 'Your WFH request was sent for review.')
            ->with('submitted_request_id', $wfhRequest->id);
    }

    public function show(WfhRequest $wfhRequest): View
    {
        Gate::authorize('view', $wfhRequest);

        return view('wfh.show-request', compact('wfhRequest'));
    }

    public function downloadSupportingDocument(WfhRequest $wfhRequest): StreamedResponse
    {
        Gate::authorize('viewDocument', $wfhRequest);

        return $this->downloadDocument($wfhRequest->supporting_document, 'wfh-supporting-document');
    }

    public function downloadReviewerDocument(WfhRequest $wfhRequest): StreamedResponse
    {
        Gate::authorize('viewDocument', $wfhRequest);

        return $this->downloadDocument($wfhRequest->reviewer_document, 'wfh-reviewer-document');
    }

    public function approval(): View
    {
        Gate::authorize('viewAny', WfhRequest::class);

        $pendingRequests = WfhRequest::query()
            ->with(['employee.user', 'employee.department'])
            ->where('status', 'pending')
            ->oldest('created_at')
            ->get();

        $recentDecisions = WfhRequest::query()
            ->with('employee.user')
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('updated_at')
            ->limit(8)
            ->get();

        return view('wfh.approval', compact('pendingRequests', 'recentDecisions'));
    }

    public function review(WfhRequest $wfhRequest): View
    {
        Gate::authorize('approve', $wfhRequest);

        abort_unless($wfhRequest->status === 'pending', 404);

        $wfhRequest->load(['employee.user', 'employee.department']);

        return view('wfh.review-request', compact('wfhRequest'));
    }

    public function updateApproval(Request $request, WfhRequest $wfhRequest, AuditLogger $auditLogger): RedirectResponse
    {
        Gate::authorize('approve', $wfhRequest);

        if ($wfhRequest->status !== 'pending') {
            return redirect()->route('wfh.approval')
                ->with('approval_error', 'This WFH request has already been reviewed.');
        }

        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'reviewer_document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:2048'],
        ]);

        $approver = $request->user()->employee;

        if (! $approver) {
            abort(403, 'An employee profile is required to approve WFH requests.');
        }

        $reviewerDocument = $wfhRequest->reviewer_document;

        if ($request->hasFile('reviewer_document')) {
            $reviewerDocument = $request->file('reviewer_document')->store('wfh_reviewer_documents', 'local');
        }

        $wfhRequest->update([
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? null,
            'reviewer_document' => $reviewerDocument,
            'approver_id' => $approver->id,
            'approved_at' => $data['status'] === 'approved' ? now() : null,
        ]);

        $auditLogger->record(
            $request->user(),
            'wfh_request.'.$data['status'],
            $wfhRequest,
            $data['status'] === 'approved' ? 'Approved a Work From Home request.' : 'Rejected a Work From Home request.',
            [
                'employee' => $wfhRequest->employee->first_name.' '.$wfhRequest->employee->last_name,
                'previous_status' => 'pending',
                'new_status' => $data['status'],
                'date_from' => $wfhRequest->date_from->toDateString(),
                'date_to' => $wfhRequest->date_to->toDateString(),
                'decision_note' => $data['remarks'] ?? null,
                'reviewer_document_uploaded' => $request->hasFile('reviewer_document'),
            ],
            $request,
        );

        $wfhRequest->load('employee.user');
        $wfhRequest->employee->user?->notify(new WfhRequestReviewed($wfhRequest));

        $message = $data['status'] === 'approved'
            ? 'WFH request approved successfully.'
            : 'WFH request was not approved.';

        return redirect()->route('wfh.approval')->with('approval_success', $message);
    }

    private function downloadDocument(?string $path, string $downloadName): StreamedResponse
    {
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($path, $downloadName.($extension ? '.'.$extension : ''));
    }
}
