<?php

namespace App\Http\Controllers;

use App\Models\WfhRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

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

    public function store(Request $request)
    {
        $employee = auth()->user()->employee;
        if (! $employee) {
            abort(404, 'Employee profile not found');
        }

        $request->validate([
            'request_type' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'reason' => 'required|string|max:500',
            'supporting_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except('supporting_document');

        // Format time if provided
        if ($request->filled('start_time')) {
            $data['start_time'] = $request->start_time.':00';
        }
        if ($request->filled('end_time')) {
            $data['end_time'] = $request->end_time.':00';
        }

        if ($request->hasFile('supporting_document')) {
            $file = $request->file('supporting_document');
            $path = $file->store('wfh_documents', 'public');
            $data['supporting_document'] = $path;
        }

        $data['employee_id'] = $employee->id;
        $data['status'] = 'pending';

        WfhRequest::create($data);

        return redirect()->route('wfh.my-requests')
            ->with('success', 'WFH request submitted successfully.');
    }

    public function show(WfhRequest $wfhRequest): View
    {
        Gate::authorize('view', $wfhRequest);

        return view('wfh.show-request', compact('wfhRequest'));
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

    public function updateApproval(Request $request, WfhRequest $wfhRequest): RedirectResponse
    {
        Gate::authorize('approve', $wfhRequest);

        if ($wfhRequest->status !== 'pending') {
            return redirect()->route('wfh.approval')
                ->with('approval_error', 'This WFH request has already been reviewed.');
        }

        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $approver = $request->user()->employee;

        if (! $approver) {
            abort(403, 'An employee profile is required to approve WFH requests.');
        }

        $wfhRequest->update([
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? null,
            'approver_id' => $approver->id,
            'approved_at' => $data['status'] === 'approved' ? now() : null,
        ]);

        $message = $data['status'] === 'approved'
            ? 'WFH request approved successfully.'
            : 'WFH request was not approved.';

        return redirect()->route('wfh.approval')->with('approval_success', $message);
    }
}
