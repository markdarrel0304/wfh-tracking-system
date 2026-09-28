<?php

namespace App\View\Components;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class calendar extends Component
{
    public $currentMonth;

    public $selectedDate;

    public $year;

    public $month;

    public function __construct($selectedDate = null)
    {
        $this->selectedDate = $selectedDate ? Carbon::parse($selectedDate) : Carbon::now();
        $this->year = $this->selectedDate->year;
        $this->month = $this->selectedDate->month;
        $this->currentMonth = Carbon::create($this->year, $this->month, 1);
    }

    public function getDaysInMonth()
    {
        return $this->currentMonth->daysInMonth;
    }

    public function getFirstDayOfMonth()
    {
        return $this->currentMonth->dayOfWeekIso; // 1 = Monday, 7 = Sunday
    }

    public function getPreviousMonthDays()
    {
        $firstDay = $this->getFirstDayOfMonth();
        if ($firstDay === 7) {
            return 0; // Sunday is 7, so no previous month days needed
        }

        return $firstDay - 1; // Adjust for Monday-first week
    }

    public function getMonthName()
    {
        return $this->currentMonth->format('F Y');
    }

    public function getCalendarDays()
    {
        $days = [];
        $totalDays = $this->getDaysInMonth();
        $previousMonthDays = $this->getPreviousMonthDays();

        // Add previous month days
        $previousMonth = $this->currentMonth->copy()->subMonth();
        for ($i = $previousMonthDays; $i > 0; $i--) {
            $days[] = [
                'day' => $previousMonth->daysInMonth - $i + 1,
                'isCurrentMonth' => false,
                'isToday' => false,
                'isSelected' => false,
            ];
        }

        // Add current month days
        $today = Carbon::now();
        for ($day = 1; $day <= $totalDays; $day++) {
            $date = Carbon::create($this->year, $this->month, $day);
            $days[] = [
                'day' => $day,
                'isCurrentMonth' => true,
                'isToday' => $date->isSameDay($today),
                'isSelected' => $date->isSameDay($this->selectedDate),
            ];
        }

        // Add next month days to complete the grid (42 cells total for 6 rows)
        $remainingDays = 42 - count($days);
        $nextMonth = $this->currentMonth->copy()->addMonth();
        for ($day = 1; $day <= $remainingDays; $day++) {
            $days[] = [
                'day' => $day,
                'isCurrentMonth' => false,
                'isToday' => false,
                'isSelected' => false,
            ];
        }

        return $days;
    }

    public function render(): View|Closure|string
    {
        return view('components.calendar', [
            'monthName' => $this->getMonthName(),
            'calendarDays' => $this->getCalendarDays(),
            'selectedDate' => $this->selectedDate->format('Y-m-d'),
        ]);
    }
}
