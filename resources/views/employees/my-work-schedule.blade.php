@php
    use Carbon\Carbon;

    $displayMonth = Carbon::create($year, $month, 1);
    $previousMonth = $displayMonth->copy()->subMonth();
    $leadingDays = $displayMonth->dayOfWeek;
    $trailingDays = (42 - ($leadingDays + $displayMonth->daysInMonth)) % 7;
    $today = Carbon::today();
    $formatMinutes = static fn (int $minutes): string => sprintf('%dh %02dm', intdiv($minutes, 60), $minutes % 60);
    $statusClasses = [
        'work_day' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300',
        'rest_day' => 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300',
        'holiday' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-300',
        'scheduled' => 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-900 dark:bg-blue-950/50 dark:text-blue-300',
    ];
    $statusDots = ['work_day' => 'bg-emerald-500', 'rest_day' => 'bg-slate-400', 'holiday' => 'bg-amber-400', 'scheduled' => 'bg-blue-500'];
@endphp

<x-dashboard-layout title="My Work Schedule">
    <div class="mx-auto max-w-6xl space-y-6 pb-8">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-6 dark:border-slate-800 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400">Work planning</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">My schedule</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600 dark:text-slate-300">Your approved WFH requests are your work schedule. The dates and hours shown here are the same ones approved by your administrator.</p>
            </div>
            <a href="{{ route('wfh.requests.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">New WFH request</a>
        </header>

        <section class="overflow-hidden rounded-2xl border border-blue-200 bg-white shadow-sm dark:border-blue-900/70 dark:bg-slate-900">
            <div class="flex flex-col gap-5 border-b border-blue-100 px-6 py-5 dark:border-slate-800 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400">Effective today</p>
                    @if ($todayWfhRequest)
                        <h2 class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">Approved WFH workday</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ Carbon::parse($todayWfhRequest->start_time)->format('g:i A') }} – {{ Carbon::parse($todayWfhRequest->end_time)->format('g:i A') }} · paid time excludes the 12:00 PM–1:00 PM lunch break.</p>
                    @else
                        <h2 class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">No approved WFH schedule today</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Submit a WFH request and wait for approval before this day is available for remote attendance.</p>
                    @endif
                </div>
                <span class="w-fit rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950 dark:text-blue-300">Request based</span>
            </div>
            @if ($todayWfhRequest)
                <div class="px-6 py-5">
                    <p class="text-sm text-slate-600 dark:text-slate-300"><span class="font-semibold text-slate-900 dark:text-white">Approved period:</span> {{ $todayWfhRequest->date_from->format('M j') }} – {{ $todayWfhRequest->date_to->format('M j, Y') }}</p>
                </div>
            @endif
        </section>

        @if ($upcomingWfhRequests->isNotEmpty())
            <section class="rounded-2xl border border-violet-200 bg-violet-50/60 px-6 py-5 dark:border-violet-900/70 dark:bg-violet-950/30">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-violet-700 dark:text-violet-300">Upcoming approved WFH</p>
                <div class="mt-2 space-y-2">@foreach ($upcomingWfhRequests as $wfhRequest)<p class="text-sm text-violet-950 dark:text-violet-100"><span class="font-semibold">{{ $wfhRequest->date_from->format('M j') }} – {{ $wfhRequest->date_to->format('M j, Y') }}:</span> {{ Carbon::parse($wfhRequest->start_time)->format('g:i A') }} – {{ Carbon::parse($wfhRequest->end_time)->format('g:i A') }}</p>@endforeach</div>
            </section>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><p class="text-sm text-slate-500 dark:text-slate-400">Approved WFH days</p><p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $weekWorkDays }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">for this week</p></section>
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><p class="text-sm text-slate-500 dark:text-slate-400">Estimated paid time</p><p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $formatMinutes($weekEstimatedMinutes) }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">lunch excluded</p></section>
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><p class="text-sm text-slate-500 dark:text-slate-400">Rest days</p><p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $weekOffDays }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">including holidays</p></section>
        </div>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-4">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <a href="{{ route('my-work-schedule', ['year' => $month === 1 ? $year - 1 : $year, 'month' => $month === 1 ? 12 : $month - 1]) }}" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Previous month">‹</a>
                    <h2 class="font-semibold text-slate-900 dark:text-white">{{ $displayMonth->format('F Y') }}</h2>
                    <a href="{{ route('my-work-schedule', ['year' => $month === 12 ? $year + 1 : $year, 'month' => $month === 12 ? 1 : $month + 1]) }}" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Next month">›</a>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-7 gap-1 text-center text-xs font-medium text-slate-400">@foreach (['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $dayName)<span class="py-2">{{ $dayName }}</span>@endforeach</div>
                    <div class="grid grid-cols-7 gap-1 text-center text-sm">
                        @for ($day = $leadingDays; $day > 0; $day--)<span class="p-2 text-slate-300 dark:text-slate-700">{{ $previousMonth->daysInMonth - $day + 1 }}</span>@endfor
                        @for ($day = 1; $day <= $displayMonth->daysInMonth; $day++)
                            @php $date = Carbon::create($year, $month, $day); $dateString = $date->toDateString(); $plan = $dayPlans[$dateString]; @endphp
                            <a href="{{ route('my-work-schedule', ['year' => $year, 'month' => $month, 'date' => $dateString]) }}" class="relative rounded-lg py-2 transition {{ $date->isSameDay($selectedDate) ? 'bg-blue-600 font-semibold text-white' : ($date->isSameDay($today) ? 'bg-blue-50 font-semibold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800') }}">{{ $day }}<span class="mx-auto mt-1 block h-1.5 w-1.5 rounded-full {{ $date->isSameDay($selectedDate) ? 'bg-white' : ($statusDots[$plan['status']] ?? 'bg-slate-400') }}"></span></a>
                        @endfor
                        @for ($day = 1; $day <= $trailingDays; $day++)<span class="p-2 text-slate-300 dark:text-slate-700">{{ $day }}</span>@endfor
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-2 border-t border-slate-100 pt-4 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400"><p class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Approved WFH</p><p class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-slate-400"></span>No WFH</p><p class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-amber-400"></span>Holiday</p></div>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-5">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800"><h2 class="font-semibold text-slate-900 dark:text-white">Week of {{ $weekDates->first()->format('M d') }} – {{ $weekDates->last()->format('M d') }}</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Only approved WFH requests create a remote workday.</p></div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($weekDates as $date)
                        @php $plan = $dayPlans[$date->toDateString()]; @endphp
                        <a href="{{ route('my-work-schedule', ['year' => $date->year, 'month' => $date->month, 'date' => $date->toDateString()]) }}" class="flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/70 {{ $date->isSameDay($selectedDate) ? 'bg-blue-50 dark:bg-blue-950/30' : '' }}">
                            <div class="w-12 shrink-0 text-center"><p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ $date->format('D') }}</p><p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ $date->format('d') }}</p></div>
                            <div class="min-w-0 flex-1"><p class="font-medium text-slate-800 dark:text-slate-100">{{ $date->isToday() ? 'Today' : $date->format('l') }}</p><p class="mt-1 truncate text-sm text-slate-500 dark:text-slate-400">{{ $plan['time_in'] ? Carbon::parse($plan['time_in'])->format('g:i A').' – '.Carbon::parse($plan['time_out'])->format('g:i A') : $plan['label'] }}</p></div>
                            <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$plan['status']] ?? $statusClasses['rest_day'] }}">{{ $plan['status'] === 'work_day' ? 'Work day' : $plan['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <aside class="lg:col-span-3 lg:sticky lg:top-6">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400">Selected day</p>
                    <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">{{ $selectedDate->format('D, M d') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $selectedDate->format('Y') }}</p>
                    <div class="mt-5 space-y-4 border-t border-slate-100 pt-5 dark:border-slate-800">
                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$selectedDayPlan['status']] ?? $statusClasses['rest_day'] }}">{{ $selectedDayPlan['status'] === 'work_day' ? 'Work day' : $selectedDayPlan['label'] }}</span>
                        <dl class="space-y-3 text-sm"><div class="flex justify-between gap-3"><dt class="text-slate-500 dark:text-slate-400">Source</dt><dd class="text-right font-medium text-slate-800 dark:text-slate-100">{{ $selectedDayPlan['source'] }}</dd></div><div class="flex justify-between gap-3"><dt class="text-slate-500 dark:text-slate-400">Arrangement</dt><dd class="text-right font-medium text-slate-800 dark:text-slate-100">{{ $selectedDayPlan['request']?->request_type ?? '—' }}</dd></div><div class="flex justify-between gap-3"><dt class="text-slate-500 dark:text-slate-400">Hours</dt><dd class="text-right font-medium text-slate-800 dark:text-slate-100">{{ $selectedDayPlan['time_in'] ? Carbon::parse($selectedDayPlan['time_in'])->format('g:i A').' – '.Carbon::parse($selectedDayPlan['time_out'])->format('g:i A') : '—' }}</dd></div>@if ($selectedDayPlan['request']?->reason)<div class="border-t border-slate-100 pt-3 dark:border-slate-800"><dt class="text-slate-500 dark:text-slate-400">Request reason</dt><dd class="mt-1 text-slate-700 dark:text-slate-200">{{ $selectedDayPlan['request']->reason }}</dd></div>@endif</dl>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-dashboard-layout>
