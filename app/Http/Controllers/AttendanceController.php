<?php

namespace App\Http\Controllers;

use App\AuditLogger;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $this->employeeFor($request);
        $employee->loadMissing('workSchedule', 'workScheduleAssignments.workSchedule');
        $activeWfhRequest = $employee->approvedWfhRequestFor(today());
        $schedule = $employee->effectiveWorkScheduleFor(today());
        $todayAttendance = $employee->attendance()->with('wfhRequest')->whereDate('date', today())->first();
        $recentAttendance = $employee->attendance()->with('wfhRequest')->latest('date')->limit(5)->get();

        return view('attendance.time-in-out', compact('employee', 'activeWfhRequest', 'schedule', 'todayAttendance', 'recentAttendance'));
    }

    public function timeIn(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $employee = $this->employeeFor($request);
        $todayAttendance = $employee->attendance()->whereDate('date', today())->first();

        if ($todayAttendance?->time_in) {
            return back()->with('attendance_error', 'You have already clocked in today.');
        }

        $clockInTime = now();
        $approvedWfhRequest = $employee->approvedWfhRequestFor($clockInTime);

        if (! $approvedWfhRequest) {
            return back()->with('attendance_error', 'You can time in after an administrator approves your WFH request.');
        }
        $attendanceData = [
            'time_in' => $clockInTime->format('H:i:s'),
            'status' => $this->statusForClockIn($employee, $clockInTime),
            'wfh_request_id' => $approvedWfhRequest?->id,
        ];

        if ($todayAttendance) {
            $todayAttendance->update($attendanceData);
        } else {
            $todayAttendance = $employee->attendance()->create([
                ...$attendanceData,
                'date' => $clockInTime->toDateString(),
            ]);
        }

        $auditLogger->record(
            $request->user(),
            'attendance.time_in',
            $todayAttendance,
            'Recorded a time in.',
            [
                'employee' => $employee->first_name.' '.$employee->last_name,
                'workday' => $todayAttendance->date->toDateString(),
                'time_in' => $todayAttendance->time_in,
                'attendance_status' => $todayAttendance->status,
                'wfh_request_id' => $todayAttendance->wfh_request_id,
            ],
            $request,
        );

        return redirect()->route('attendance.time-in-out')
            ->with('attendance_success', 'You are clocked in for today.');
    }

    public function timeOut(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $employee = $this->employeeFor($request);
        $todayAttendance = $employee->attendance()->whereDate('date', today())->first();

        if (! $todayAttendance?->time_in) {
            return back()->with('attendance_error', 'Clock in before recording your time out.');
        }

        if ($todayAttendance->time_out) {
            return back()->with('attendance_error', 'You have already clocked out today.');
        }

        $regularTimeOut = $this->scheduleTime($employee, 'time_out', '17:00:00');

        if (now()->lessThan($regularTimeOut)) {
            return back()->with('attendance_error', 'Use the Early clock out option before '.$regularTimeOut->format('g:i A').'.');
        }

        $todayAttendance->update([
            'time_out' => now()->format('H:i:s'),
            'early_out' => false,
        ]);

        $auditLogger->record($request->user(), 'attendance.time_out', $todayAttendance, 'Recorded a regular time out.', [
            'employee' => $employee->first_name.' '.$employee->last_name,
            'workday' => $todayAttendance->date->toDateString(),
            'time_out' => $todayAttendance->time_out,
        ], $request);

        return redirect()->route('attendance.time-in-out')
            ->with('attendance_success', 'Your time out was recorded.');
    }

    public function earlyTimeOut(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $employee = $this->employeeFor($request);
        $todayAttendance = $employee->attendance()->whereDate('date', today())->first();

        if (! $todayAttendance?->time_in) {
            return back()->with('attendance_error', 'Clock in before recording an early time out.');
        }

        if ($todayAttendance->time_out) {
            return back()->with('attendance_error', 'You have already clocked out today.');
        }

        $regularTimeOut = $this->scheduleTime($employee, 'time_out', '17:00:00');

        if (now()->greaterThanOrEqualTo($regularTimeOut)) {
            return back()->with('attendance_error', 'Use the regular clock out option from '.$regularTimeOut->format('g:i A').'.');
        }

        $todayAttendance->update([
            'time_out' => now()->format('H:i:s'),
            'early_out' => true,
        ]);

        $auditLogger->record($request->user(), 'attendance.early_time_out', $todayAttendance, 'Recorded an early time out.', [
            'employee' => $employee->first_name.' '.$employee->last_name,
            'workday' => $todayAttendance->date->toDateString(),
            'time_out' => $todayAttendance->time_out,
            'early_departure' => true,
        ], $request);

        return redirect()->route('attendance.time-in-out')
            ->with('attendance_success', 'Your early time out was recorded. Overtime is unavailable for this workday.');
    }

    public function startOvertime(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $employee = $this->employeeFor($request);
        $attendance = $employee->attendance()->whereDate('date', today())->first();

        if (! $attendance?->time_out) {
            return back()->with('attendance_error', 'Complete your regular time out before starting overtime.');
        }

        if (! $this->isOvertimeEligible($attendance, $employee)) {
            return back()->with('attendance_error', $attendance->early_out
                ? 'Overtime is unavailable because this workday was recorded as an early departure.'
                : 'Overtime is available only when you clocked in at '.$this->scheduleTime($employee, 'time_in', '08:00:00')->format('g:i A').' or earlier.');
        }

        if ($attendance->overtime_in) {
            return back()->with('attendance_error', 'Your overtime start has already been recorded.');
        }

        $overtimeStart = $this->scheduleTime($employee, 'overtime_start', '17:30:00');
        $maximumEnd = $this->overtimeMaximumEnd($employee);
        $latestStart = $maximumEnd->copy()->subMinutes($this->overtimeMinimumMinutes($employee));

        if (now()->lessThan($overtimeStart)) {
            return back()->with('attendance_error', 'Overtime starts at '.$overtimeStart->format('g:i A').'. Please wait for the interval after regular work.');
        }

        if (now()->greaterThan($latestStart)) {
            return back()->with('attendance_error', 'Overtime can no longer start because the '.$maximumEnd->format('g:i A').' maximum would not allow the required '.$this->overtimeMinimumMinutes($employee).' minutes.');
        }

        $attendance->update(['overtime_in' => now()->format('H:i:s')]);

        $auditLogger->record($request->user(), 'attendance.overtime_started', $attendance, 'Started overtime.', [
            'employee' => $employee->first_name.' '.$employee->last_name,
            'workday' => $attendance->date->toDateString(),
            'overtime_in' => $attendance->overtime_in,
        ], $request);

        return redirect()->route('attendance.time-in-out')
            ->with('attendance_success', 'Overtime started at '.now()->format('g:i A').'. Minimum overtime is '.$this->overtimeMinimumMinutes($employee).' minutes.');
    }

    public function endOvertime(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $employee = $this->employeeFor($request);
        $attendance = $employee->attendance()->whereDate('date', today())->first();

        if (! $attendance?->overtime_in) {
            return back()->with('attendance_error', 'Start overtime before recording its end time.');
        }

        if ($attendance->overtime_out) {
            return back()->with('attendance_error', 'Your overtime end has already been recorded.');
        }

        $overtimeStart = Carbon::parse(today()->toDateString().' '.$attendance->overtime_in);
        $minimumEnd = $overtimeStart->copy()->addMinutes($this->overtimeMinimumMinutes($employee));

        if (now()->lessThan($minimumEnd)) {
            return back()->with('attendance_error', 'Overtime can be ended from '.$minimumEnd->format('g:i A').' to meet the '.$this->overtimeMinimumLabel($employee).' minimum.');
        }

        $maximumEnd = $this->overtimeMaximumEnd($employee);
        $recordedEnd = now()->greaterThan($maximumEnd) ? $maximumEnd : now();

        $attendance->update(['overtime_out' => $recordedEnd->format('H:i:s')]);

        $auditLogger->record($request->user(), 'attendance.overtime_ended', $attendance, 'Ended overtime.', [
            'employee' => $employee->first_name.' '.$employee->last_name,
            'workday' => $attendance->date->toDateString(),
            'overtime_in' => $attendance->overtime_in,
            'overtime_out' => $attendance->overtime_out,
        ], $request);

        return redirect()->route('attendance.time-in-out')
            ->with('attendance_success', now()->greaterThan($maximumEnd)
                ? 'Overtime ended and was capped at the '.$maximumEnd->format('g:i A').' maximum.'
                : 'Your overtime end was recorded.');
    }

    public function history(Request $request): View
    {
        $employee = $this->employeeFor($request);
        $attendances = $employee->attendance()->with('wfhRequest')->latest('date')->paginate(15);

        return view('attendance.daily', compact('attendances'));
    }

    private function employeeFor(Request $request): Employee
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            abort(404, 'Employee profile not found.');
        }

        return $employee;
    }

    private function statusForClockIn(Employee $employee, Carbon $clockInTime): string
    {
        $scheduledTimeIn = $employee->approvedWfhRequestFor($clockInTime)?->start_time
            ?? $employee->effectiveWorkScheduleFor($clockInTime)?->time_in;

        if (! $scheduledTimeIn) {
            return 'present';
        }

        return $clockInTime->format('H:i:s') > $scheduledTimeIn ? 'late' : 'present';
    }

    private function isOvertimeEligible(Attendance $attendance, Employee $employee): bool
    {
        return $attendance->time_in !== null
            && $attendance->time_in <= $this->scheduleTime($employee, 'time_in', '08:00:00')->format('H:i:s')
            && ! $attendance->early_out;
    }

    private function scheduleTime(Employee $employee, string $field, string $fallback): Carbon
    {
        $wfhRequest = $employee->approvedWfhRequestFor(today());

        if ($field === 'overtime_start' && $wfhRequest?->end_time) {
            return Carbon::parse(today()->toDateString().' '.$wfhRequest->end_time)->addMinutes(30);
        }

        $time = match ($field) {
            'time_in' => $wfhRequest?->start_time,
            'time_out' => $wfhRequest?->end_time,
            default => null,
        } ?? $employee->effectiveWorkScheduleFor(today())?->{$field} ?? $fallback;

        return Carbon::parse(today()->toDateString().' '.$time);
    }

    private function overtimeMinimumMinutes(Employee $employee): int
    {
        return $employee->effectiveWorkScheduleFor(today())?->overtime_minimum_minutes ?? 120;
    }

    private function overtimeMaximumEnd(Employee $employee): Carbon
    {
        $maximumMinutes = $employee->effectiveWorkScheduleFor(today())?->overtime_maximum_minutes ?? 180;

        return $this->scheduleTime($employee, 'overtime_start', '17:30:00')
            ->addMinutes($maximumMinutes);
    }

    private function overtimeMinimumLabel(Employee $employee): string
    {
        $minutes = $this->overtimeMinimumMinutes($employee);

        if ($minutes === 120) {
            return 'two-hour';
        }

        return $minutes.'-minute';
    }
}
