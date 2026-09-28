<?php

namespace App\View\Components;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ScheduleDatePicker extends Component
{
    public $name;

    public $value;

    public $label;

    public $required;

    public $selectedDate;

    public $year;

    public $month;

    public $years;

    public $months;

    public $yearRange;

    public function __construct($name, $value = null, $label = null, $required = false, $yearRange = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->label = $label;
        $this->required = $required;
        $this->selectedDate = $value ? Carbon::parse($value) : Carbon::now();
        $this->year = $this->selectedDate->year;
        $this->month = $this->selectedDate->month;

        $currentYear = now()->year;
        $this->years = $yearRange
            ? range($currentYear - $yearRange['past'], $currentYear + $yearRange['future'])
            : range($currentYear - 1, $currentYear + 1);
        rsort($this->years);

        $this->months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
        $this->yearRange = $yearRange;
    }

    public function getDaysInMonth()
    {
        return Carbon::create($this->year, $this->month, 1)->daysInMonth;
    }

    public function getFirstDayOfMonth()
    {
        return Carbon::create($this->year, $this->month, 1)->dayOfWeekIso;
    }

    public function getCalendarDays()
    {
        $days = [];
        $totalDays = $this->getDaysInMonth();
        $firstDay = $this->getFirstDayOfMonth();

        $previousMonth = Carbon::create($this->year, $this->month, 1)->subMonth();
        for ($i = $firstDay === 7 ? 0 : $firstDay - 1; $i > 0; $i--) {
            $days[] = [
                'day' => $previousMonth->daysInMonth - $i + 1,
                'isCurrentMonth' => false,
                'isToday' => false,
                'isSelected' => false,
            ];
        }

        $today = Carbon::now();
        for ($day = 1; $day <= $totalDays; $day++) {
            $date = Carbon::create($this->year, $this->month, $day);
            $days[] = [
                'day' => $day,
                'isCurrentMonth' => true,
                'isToday' => $date->isSameDay($today),
                'isSelected' => $this->value && $date->isSameDay(Carbon::parse($this->value)),
            ];
        }

        $remainingDays = 42 - count($days);
        $nextMonth = Carbon::create($this->year, $this->month, 1)->addMonth();
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
        return view('components.schedule-date-picker', [
            'years' => $this->years,
            'months' => $this->months,
            'calendarDays' => $this->getCalendarDays(),
            'selectedDate' => $this->value ? $this->selectedDate->toDateString() : '',
        ]);
    }
}
