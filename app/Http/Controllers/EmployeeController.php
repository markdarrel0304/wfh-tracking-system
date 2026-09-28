<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateEmployeeProfileRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\WfhRequest;
use App\Models\WorkScheduleEntry;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Employee::class);

        $employees = Employee::with('department')->get();
        $departments = Department::all();

        return view('employees.index', compact('employees', 'departments'));
    }

    public function show(Employee $employee): View
    {
        Gate::authorize('view', $employee);

        $employee->load('department', 'user');
        $departments = Department::all();

        return view('employees.profile', compact('employee', 'departments'));
    }

    public function update(UpdateEmployeeProfileRequest $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $employee->update($request->validated());

        return redirect()->route('employees.profile', $employee)
            ->with('success', 'Profile updated successfully.');
    }

    public function myProfile(): View
    {
        $employee = auth()->user()->employee;
        if (! $employee) {
            // Try to find employee by user_id
            $employee = Employee::where('user_id', auth()->id())->first();
            if (! $employee) {
                abort(404, 'Employee profile not found');
            }
        }
        $employee->load('department', 'user');
        $departments = Department::all();
        $approvedWfhRequests = $employee->wfhRequests()
            ->where('status', 'approved')
            ->whereDate('date_from', '<=', now()->endOfWeek())
            ->whereDate('date_to', '>=', now()->startOfWeek())
            ->orderBy('date_from')
            ->get();

        return view('employees.my-profile', compact('employee', 'departments', 'approvedWfhRequests'));
    }

    public function updateMyProfile(UpdateEmployeeProfileRequest $request)
    {
        $employee = auth()->user()->employee;
        if (! $employee) {
            $employee = Employee::where('user_id', auth()->id())->first();
            if (! $employee) {
                abort(404, 'Employee profile not found');
            }
        }

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $path = $file->store('photos', 'public');
            $data['photo'] = $path;
        }

        $employee->update($data);

        return redirect()->route('my-profile')
            ->with('profile_success', 'Profile updated successfully.');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = auth()->user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('my-profile')
            ->withFragment('change-password')
            ->with('password_success', 'Password changed successfully.');
    }

    public function myWorkSchedule(Request $request): View
    {
        $data = $request->validate([
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'date' => ['nullable', 'date'],
        ]);
        $employee = auth()->user()->employee;
        if (! $employee) {
            $employee = Employee::where('user_id', auth()->id())->first();
            if (! $employee) {
                abort(404, 'Employee profile not found');
            }
        }
        $employee->load('department');

        $selectedDate = Carbon::parse($data['date'] ?? now()->toDateString())->startOfDay();
        $year = $data['year'] ?? $selectedDate->year;
        $month = $data['month'] ?? $selectedDate->month;
        $displayMonth = Carbon::create($year, $month, 1)->startOfDay();
        $weekDates = collect(range(0, 6))
            ->map(fn (int $offset): Carbon => $selectedDate->copy()->startOfWeek(Carbon::MONDAY)->addDays($offset));
        $monthDates = collect(range(1, $displayMonth->daysInMonth))
            ->map(fn (int $day): Carbon => $displayMonth->copy()->day($day));
        $datesToPlan = $monthDates->merge($weekDates)->push($selectedDate)->unique(fn (Carbon $date): string => $date->toDateString());

        $holidays = Holiday::query()
            ->where('is_active', true)
            ->where(function ($query) use ($year): void {
                $query->whereYear('date', $year)->orWhere('is_recurring', true);
            })
            ->get();
        $holidayByDate = $datesToPlan->mapWithKeys(function (Carbon $date) use ($holidays): array {
            $holiday = $holidays->first(function (Holiday $holiday) use ($date): bool {
                return $holiday->is_recurring
                    ? $holiday->date->format('m-d') === $date->format('m-d')
                    : $holiday->date->isSameDay($date);
            });

            return [$date->toDateString() => $holiday];
        });
        $firstPlannedDate = $datesToPlan->min(fn (Carbon $date): Carbon => $date);
        $lastPlannedDate = $datesToPlan->max(fn (Carbon $date): Carbon => $date);
        $approvedWfhRequests = $employee->wfhRequests()
            ->where('status', 'approved')
            ->whereDate('date_from', '<=', $lastPlannedDate)
            ->whereDate('date_to', '>=', $firstPlannedDate)
            ->orderBy('date_from')
            ->get();
        $dayPlans = $datesToPlan->mapWithKeys(fn (Carbon $date): array => [
            $date->toDateString() => $this->wfhPlanFor($date, $approvedWfhRequests, $holidayByDate),
        ]);
        $selectedDayPlan = $dayPlans[$selectedDate->toDateString()];
        $weekPlans = $weekDates->map(fn (Carbon $date): array => $dayPlans[$date->toDateString()]);
        $weekWorkDays = $weekPlans->where('status', 'work_day')->count();
        $weekOffDays = $weekPlans->whereIn('status', ['rest_day', 'holiday'])->count();
        $weekEstimatedMinutes = $weekPlans->sum('paid_minutes');
        $todayWfhRequest = $approvedWfhRequests->first(fn (WfhRequest $wfhRequest): bool => today()->betweenIncluded($wfhRequest->date_from, $wfhRequest->date_to));
        $upcomingWfhRequests = $employee->wfhRequests()
            ->where('status', 'approved')
            ->whereDate('date_from', '>', today())
            ->orderBy('date_from')
            ->get()
            ->values();

        return view('employees.my-work-schedule', compact(
            'employee',
            'year',
            'month',
            'holidays',
            'selectedDate',
            'weekDates',
            'dayPlans',
            'selectedDayPlan',
            'weekWorkDays',
            'weekOffDays',
            'weekEstimatedMinutes',
            'todayWfhRequest',
            'upcomingWfhRequests',
        ));
    }

    /**
     * @param  Collection<int, WfhRequest>  $approvedWfhRequests
     * @param  Collection<string, ?Holiday>  $holidayByDate
     * @return array{status: string, label: string, source: string, request: ?WfhRequest, holiday: ?Holiday, time_in: ?string, time_out: ?string, paid_minutes: int}
     */
    private function wfhPlanFor(CarbonInterface $date, Collection $approvedWfhRequests, Collection $holidayByDate): array
    {
        $dateKey = $date->toDateString();
        $holiday = $holidayByDate->get($dateKey);
        $wfhRequest = $approvedWfhRequests->first(fn (WfhRequest $request): bool => $date->betweenIncluded($request->date_from, $request->date_to));

        if ($holiday !== null) {
            return [
                'status' => 'holiday',
                'label' => $holiday->name,
                'source' => 'Company holiday',
                'request' => $wfhRequest,
                'holiday' => $holiday,
                'time_in' => null,
                'time_out' => null,
                'paid_minutes' => 0,
            ];
        }

        if ($wfhRequest !== null) {
            return [
                'status' => 'work_day',
                'label' => 'Approved WFH',
                'source' => 'Approved WFH request',
                'request' => $wfhRequest,
                'holiday' => null,
                'time_in' => $wfhRequest->start_time,
                'time_out' => $wfhRequest->end_time,
                'paid_minutes' => $this->paidMinutes($wfhRequest->start_time, $wfhRequest->end_time),
            ];
        }

        return [
            'status' => 'rest_day',
            'label' => 'No approved WFH',
            'source' => 'No approved WFH request',
            'request' => null,
            'holiday' => null,
            'time_in' => null,
            'time_out' => null,
            'paid_minutes' => 0,
        ];
    }

    private function paidMinutes(?string $timeIn, ?string $timeOut): int
    {
        if ($timeIn === null || $timeOut === null) {
            return 0;
        }

        $start = Carbon::parse($timeIn);
        $end = Carbon::parse($timeOut);
        $minutes = $start->diffInMinutes($end);

        return $minutes >= 60 ? $minutes - 60 : $minutes;
    }

    public function storeMyScheduleEntry(Request $request)
    {
        $employee = auth()->user()->employee;
        if (! $employee) {
            $employee = Employee::where('user_id', auth()->id())->first();
            if (! $employee) {
                abort(404, 'Employee profile not found');
            }
        }

        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
            'time_in' => 'nullable',
            'time_out' => 'nullable',
            'status' => 'required|in:scheduled,work_day,rest_day,holiday',
            'remark' => 'nullable|string|max:1000',
        ]);

        // Auto-detect day from date
        $date = Carbon::parse($request->date);
        $day = $date->format('l');

        // Auto-detect if the date is a holiday
        $holiday = Holiday::query()->where('is_active', true)->whereDate('date', $date)->first();

        $status = $request->status;
        if ($holiday && $status !== 'holiday') {
            $status = 'holiday';
        }

        WorkScheduleEntry::create([
            'employee_id' => $employee->id,
            'day' => $day,
            'date' => $request->date,
            'shift' => $request->shift,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'status' => $status,
            'remark' => $request->remark,
        ]);

        return redirect()->route('my-work-schedule', ['date' => $request->date])
            ->with('success', 'Schedule entry added successfully.');
    }

    public function updateMyScheduleEntry(Request $request, $id)
    {
        $employee = auth()->user()->employee;
        if (! $employee) {
            $employee = Employee::where('user_id', auth()->id())->first();
            if (! $employee) {
                abort(404, 'Employee profile not found');
            }
        }

        $entry = $employee->workScheduleEntries()->findOrFail($id);

        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
            'time_in' => 'nullable',
            'time_out' => 'nullable',
            'status' => 'required|in:scheduled,work_day,rest_day,holiday',
            'remark' => 'nullable|string|max:1000',
        ]);

        // Auto-detect day from date
        $date = Carbon::parse($request->date);
        $day = $date->format('l');

        // Auto-detect if the date is a holiday
        $holiday = Holiday::query()->where('is_active', true)->whereDate('date', $date)->first();

        $status = $request->status;
        if ($holiday && $status !== 'holiday') {
            $status = 'holiday';
        }

        $entry->update([
            'day' => $day,
            'date' => $request->date,
            'shift' => $request->shift,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'status' => $status,
            'remark' => $request->remark,
        ]);

        return redirect()->route('my-work-schedule', ['date' => $request->date])
            ->with('success', 'Schedule entry updated successfully.');
    }

    public function workSchedule(Employee $employee): View
    {
        $employee->load('department');
        $departments = Department::all();
        $scheduleEntries = $employee->workScheduleEntries()->orderBy('date')->get();

        return view('employees.work-schedule', compact('employee', 'departments', 'scheduleEntries'));
    }

    public function storeScheduleEntry(Request $request, Employee $employee)
    {
        $request->validate([
            'day' => 'required|string',
            'date' => 'required|date',
            'shift' => 'required|string',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:scheduled,work_day,rest_day,holiday',
            'remark' => 'nullable|string|max:1000',
        ]);

        WorkScheduleEntry::create([
            'employee_id' => $employee->id,
            'day' => $request->day,
            'date' => $request->date,
            'shift' => $request->shift,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'status' => $request->status,
            'remark' => $request->remark,
        ]);

        $redirectRoute = $employee->id === auth()->user()->employee->id ? 'my-work-schedule' : 'employees.work-schedule';

        return redirect()->route($redirectRoute, $employee)
            ->with('success', 'Schedule entry added successfully.');
    }

    public function updateScheduleEntry(Request $request, WorkScheduleEntry $scheduleEntry)
    {
        $request->validate([
            'shift' => 'required|string',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:scheduled,work_day,rest_day,holiday',
            'remark' => 'nullable|string|max:1000',
        ]);

        $scheduleEntry->update($request->only('shift', 'time_in', 'time_out', 'status', 'remark'));

        $employee = $scheduleEntry->employee;
        $redirectRoute = $employee->id === auth()->user()->employee->id ? 'my-work-schedule' : 'employees.work-schedule';

        return redirect()->route($redirectRoute, $employee)
            ->with('success', 'Schedule entry updated successfully.');
    }

    public function destroyMyScheduleEntry(WorkScheduleEntry $scheduleEntry)
    {
        $employee = auth()->user()->employee;
        if (! $employee) {
            $employee = Employee::where('user_id', auth()->id())->first();
            if (! $employee) {
                abort(404, 'Employee profile not found');
            }
        }

        if ($scheduleEntry->employee_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        $scheduleEntry->delete();

        return redirect()->route('my-work-schedule')
            ->with('success', 'Schedule entry deleted successfully.');
    }
}
