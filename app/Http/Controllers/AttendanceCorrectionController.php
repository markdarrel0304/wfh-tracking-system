<?php

namespace App\Http\Controllers;

use App\AuditLogger;
use App\Http\Requests\ReviewAttendanceCorrectionRequest;
use App\Http\Requests\StoreAttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\AttendanceCorrectionReviewed;
use App\Notifications\AttendanceCorrectionSubmitted;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceCorrectionController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $this->employeeForCurrentUser();
        Gate::authorize('viewAny', AttendanceCorrection::class);

        $attendances = $employee->attendance()
            ->with('wfhRequest')
            ->latest('date')
            ->limit(60)
            ->get();
        $corrections = $employee->attendanceCorrections()
            ->with(['attendance.wfhRequest', 'approver.user'])
            ->latest()
            ->get();

        $selectedAttendanceId = $attendances->contains('id', $request->integer('attendance_id'))
            ? $request->integer('attendance_id')
            : null;

        return view('attendance.corrections', compact('attendances', 'corrections', 'selectedAttendanceId'));
    }

    public function store(StoreAttendanceCorrectionRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $employee = $this->employeeForCurrentUser();
        Gate::authorize('create', AttendanceCorrection::class);

        $attendance = $employee->attendance()->findOrFail($request->integer('attendance_id'));

        if ($attendance->corrections()->where('status', 'pending')->exists()) {
            return back()->withInput()->with('correction_error', 'A correction for this attendance record is already awaiting review.');
        }

        $validated = $request->validated();

        $correctionData = [
            'requested_time_in' => ! empty($validated['requested_time_in'])
                ? Carbon::createFromFormat('H:i', $validated['requested_time_in'])->format('H:i:s')
                : null,
            'requested_time_out' => ! empty($validated['requested_time_out'])
                ? Carbon::createFromFormat('H:i', $validated['requested_time_out'])->format('H:i:s')
                : null,
            'reason' => $validated['reason'],
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'status' => 'pending',
        ];

        if ($request->hasFile('supporting_document')) {
            $correctionData['supporting_document'] = $request->file('supporting_document')->store('attendance_corrections', 'local');
        }

        $correction = AttendanceCorrection::create($correctionData);

        $auditLogger->record(
            $request->user(),
            'attendance_correction.submitted',
            $correction,
            'Submitted an attendance correction request.',
            [
                'employee' => $employee->first_name.' '.$employee->last_name,
                'workday' => $attendance->date->toDateString(),
                'recorded_time_in' => $attendance->time_in,
                'recorded_time_out' => $attendance->time_out,
                'requested_time_in' => $correction->requested_time_in,
                'requested_time_out' => $correction->requested_time_out,
                'reason' => $correction->reason,
                'supporting_document_uploaded' => $correction->supporting_document !== null,
            ],
            $request,
        );

        $correction->load(['employee.user', 'attendance']);
        User::query()
            ->whereIn('role', ['admin', 'supervisor'])
            ->each(fn (User $user) => $user->notify(new AttendanceCorrectionSubmitted($correction)));

        return redirect()->route('attendance.corrections')
            ->with('correction_success', 'Your attendance correction was submitted for review.');
    }

    public function approval(): View
    {
        Gate::authorize('viewAny', AttendanceCorrection::class);

        abort_unless(in_array(auth()->user()->role, ['admin', 'supervisor'], true), 403);

        $pendingCorrections = AttendanceCorrection::query()
            ->with(['employee.user', 'employee.department', 'attendance.wfhRequest'])
            ->where('status', 'pending')
            ->oldest()
            ->get();
        $recentDecisions = AttendanceCorrection::query()
            ->with(['employee.user', 'attendance', 'approver.user'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('reviewed_at')
            ->limit(8)
            ->get();

        return view('approvals.attendance-corrections', compact('pendingCorrections', 'recentDecisions'));
    }

    public function review(AttendanceCorrection $attendanceCorrection): View
    {
        Gate::authorize('approve', $attendanceCorrection);

        abort_unless($attendanceCorrection->status === 'pending', 404);

        $attendanceCorrection->load([
            'employee.user',
            'employee.department',
            'attendance',
        ]);

        return view('approvals.review-attendance-correction', compact('attendanceCorrection'));
    }

    public function downloadSupportingDocument(AttendanceCorrection $attendanceCorrection): StreamedResponse
    {
        Gate::authorize('view', $attendanceCorrection);

        abort_unless(
            $attendanceCorrection->supporting_document
            && Storage::disk('local')->exists($attendanceCorrection->supporting_document),
            404,
        );

        $extension = pathinfo($attendanceCorrection->supporting_document, PATHINFO_EXTENSION);

        return Storage::disk('local')->download(
            $attendanceCorrection->supporting_document,
            'attendance-correction-proof'.($extension ? '.'.$extension : ''),
        );
    }

    public function updateApproval(ReviewAttendanceCorrectionRequest $request, AttendanceCorrection $attendanceCorrection, AuditLogger $auditLogger): RedirectResponse
    {
        Gate::authorize('approve', $attendanceCorrection);

        $approver = $this->employeeForCurrentUser();

        $reviewedCorrection = DB::transaction(function () use ($attendanceCorrection, $request, $approver): AttendanceCorrection {
            $correction = AttendanceCorrection::query()->lockForUpdate()->findOrFail($attendanceCorrection->id);

            if ($correction->status !== 'pending') {
                abort(409, 'This attendance correction has already been reviewed.');
            }

            $attendance = Attendance::query()->lockForUpdate()->findOrFail($correction->attendance_id);
            $decision = $request->validated();

            if ($decision['status'] === 'approved') {
                $timeIn = $correction->requested_time_in ?? $attendance->time_in;
                $timeOut = $correction->requested_time_out ?? $attendance->time_out;

                if ($timeIn && $timeOut && Carbon::parse($timeOut)->lessThanOrEqualTo(Carbon::parse($timeIn))) {
                    throw ValidationException::withMessages([
                        'status' => 'The corrected time out must be after the corrected time in.',
                    ]);
                }

                $attendance->update([
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'status' => $timeIn ? $this->statusForTimeIn($attendance->employee, $attendance->date, $timeIn) : $attendance->status,
                ]);
            }

            $correction->update([
                'status' => $decision['status'],
                'remarks' => $decision['remarks'] ?? null,
                'approver_id' => $approver->id,
                'reviewed_at' => now(),
            ]);

            return $correction;
        });

        $reviewedCorrection->load('employee.user', 'attendance');

        $auditLogger->record(
            $request->user(),
            'attendance_correction.'.$reviewedCorrection->status,
            $reviewedCorrection,
            $reviewedCorrection->status === 'approved' ? 'Approved an attendance correction request.' : 'Rejected an attendance correction request.',
            [
                'employee' => $reviewedCorrection->employee->first_name.' '.$reviewedCorrection->employee->last_name,
                'previous_status' => 'pending',
                'new_status' => $reviewedCorrection->status,
                'workday' => $reviewedCorrection->attendance->date->toDateString(),
                'requested_time_in' => $reviewedCorrection->requested_time_in,
                'requested_time_out' => $reviewedCorrection->requested_time_out,
                'decision_note' => $reviewedCorrection->remarks,
            ],
            $request,
        );

        $reviewedCorrection->employee->user?->notify(new AttendanceCorrectionReviewed($reviewedCorrection));

        return redirect()->route('approvals.attendance-corrections')
            ->with('approval_success', $reviewedCorrection->status === 'approved'
                ? 'Attendance correction approved and the attendance record was updated.'
                : 'Attendance correction was not approved.');
    }

    private function employeeForCurrentUser(): Employee
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            abort(404, 'Employee profile not found.');
        }

        return $employee;
    }

    private function statusForTimeIn(Employee $employee, Carbon $date, string $timeIn): string
    {
        $employee->loadMissing('workSchedule');
        $scheduledTimeIn = $employee->workSchedule?->time_in;

        if (! $scheduledTimeIn) {
            return 'present';
        }

        return Carbon::parse($date->format('Y-m-d').' '.$timeIn)->format('H:i:s') > $scheduledTimeIn
            ? 'late'
            : 'present';
    }
}
