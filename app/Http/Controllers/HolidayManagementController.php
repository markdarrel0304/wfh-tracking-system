<?php

namespace App\Http\Controllers;

use App\AuditLogger;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Models\Employee;
use App\Models\Holiday;
use App\Notifications\HolidayScheduleUpdated;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HolidayManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $filters = $request->validate([
            'year' => ['nullable', 'integer', 'between:2020,2100'],
            'type' => ['nullable', 'in:regular,special'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
        $selectedYear = (int) ($filters['year'] ?? now()->year);
        $calendarDate = Carbon::parse(old('date', now()->toDateString()))->startOfDay();

        $holidays = Holiday::query()
            ->whereYear('date', $selectedYear)
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('is_active', $status === 'active'))
            ->orderBy('date')
            ->get();

        return view('admin.holidays', [
            'holidays' => $holidays,
            'selectedYear' => $selectedYear,
            'filters' => $filters,
            'years' => range(now()->year - 1, now()->year + 4),
            'calendarDate' => $calendarDate,
            'calendarDays' => $this->calendarDaysFor($calendarDate),
            'calendarMonths' => $this->calendarMonths(),
        ]);
    }

    public function store(StoreHolidayRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $holiday = Holiday::create($request->validated());

        $auditLogger->record($request->user(), 'holiday.created', $holiday, "Created the {$holiday->name} holiday.", [
            'date' => $holiday->date->toDateString(),
            'type' => $holiday->type,
            'is_recurring' => $holiday->is_recurring,
        ], $request);

        $this->notifyActiveEmployees($holiday, true, $request);

        return back()->with('success', 'Holiday added successfully.');
    }

    public function update(UpdateHolidayRequest $request, Holiday $holiday, AuditLogger $auditLogger): RedirectResponse
    {
        $holiday->update($request->validated());

        $auditLogger->record($request->user(), 'holiday.updated', $holiday, "Updated the {$holiday->name} holiday.", [
            'date' => $holiday->date->toDateString(),
            'type' => $holiday->type,
            'is_recurring' => $holiday->is_recurring,
            'is_active' => $holiday->is_active,
        ], $request);

        if ($holiday->wasChanged(['name', 'date', 'type', 'is_recurring', 'is_active'])) {
            $this->notifyActiveEmployees($holiday, false, $request);
        }

        return back()->with('success', 'Holiday updated successfully.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }

    private function notifyActiveEmployees(Holiday $holiday, bool $wasCreated, Request $request): void
    {
        Employee::query()
            ->where('status', 'active')
            ->with('user')
            ->get()
            ->each(function (Employee $employee) use ($holiday, $wasCreated, $request): void {
                if ($employee->user && ! $employee->user->is($request->user())) {
                    $employee->user->notify(new HolidayScheduleUpdated($holiday, $wasCreated));
                }
            });
    }

    /**
     * @return array<int, array{day: int, date: string, isCurrentMonth: bool, isSelected: bool, isToday: bool}>
     */
    private function calendarDaysFor(Carbon $selectedDate): array
    {
        $firstVisibleDate = $selectedDate->copy()->startOfMonth()->subDays($selectedDate->dayOfWeek);
        $today = today();
        $calendarDays = [];

        for ($index = 0; $index < 42; $index++) {
            $date = $firstVisibleDate->copy()->addDays($index);
            $calendarDays[] = [
                'day' => $date->day,
                'date' => $date->toDateString(),
                'isCurrentMonth' => $date->month === $selectedDate->month,
                'isSelected' => $date->isSameDay($selectedDate),
                'isToday' => $date->isSameDay($today),
            ];
        }

        return $calendarDays;
    }

    /** @return array<int, string> */
    private function calendarMonths(): array
    {
        return [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
            7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
    }
}
