<x-dashboard-layout title="Time In / Out">
    @php
        $timeIn = $schedule?->time_in ?? '08:00:00';
        $timeOut = $schedule?->time_out ?? '17:00:00';
        $overtimeStart = $schedule?->overtime_start ?? '17:30:00';
        $formatScheduleTime = static fn (string $time): string => \Carbon\Carbon::parse($time)->format('g:i A');
        $regularTimeOut = \Carbon\Carbon::parse(today()->toDateString().' '.$timeOut);
        $isClockedIn = $todayAttendance?->time_in && ! $todayAttendance?->time_out;
        $isOvertimeActive = $todayAttendance?->overtime_in && ! $todayAttendance?->overtime_out;
        $isDayComplete = $todayAttendance?->time_out && (! $todayAttendance?->overtime_in || $todayAttendance?->overtime_out);
        $attendanceStatus = $isOvertimeActive ? 'Overtime active' : ($isDayComplete ? 'Workday complete' : ($isClockedIn ? 'Clocked in' : 'Not clocked in'));
        $statusStyle = $isOvertimeActive || $isClockedIn ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : ($isDayComplete ? 'bg-slate-100 text-slate-700 ring-slate-200' : 'bg-amber-50 text-amber-700 ring-amber-100');
        $overtimeEligible = $todayAttendance?->time_in && $todayAttendance->time_in <= $timeIn && ! $todayAttendance->early_out;
        $canEarlyClockOut = now()->lessThan($regularTimeOut);
    @endphp

    <div class="mx-auto max-w-6xl space-y-6 pb-6">
        <section class="flex flex-col gap-5 rounded-2xl border border-slate-200 bg-white px-6 py-7 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Attendance</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Your workday, recorded clearly.</h2>
                <p class="mt-2 text-sm text-slate-600">{{ now()->format('l, F j, Y') }}</p>
            </div>
            <span class="inline-flex w-fit rounded-full px-3 py-1.5 text-sm font-semibold ring-1 ring-inset {{ $statusStyle }}">{{ $attendanceStatus }}</span>
        </section>

        @if (session('attendance_success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('attendance_success') }}</div>
        @endif

        @if (session('attendance_error'))
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ session('attendance_error') }}</div>
        @endif

        <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Today’s attendance</h3>
                        @if ($activeWfhRequest)
                            <p class="mt-1 text-sm text-slate-600">Your approved WFH schedule is {{ $formatScheduleTime($timeIn) }}–{{ $formatScheduleTime($timeOut) }}. Paid time automatically excludes 12:00 PM–1:00 PM.</p>
                        @else
                            <p class="mt-1 text-sm text-slate-600">No approved WFH request is active today. Submit a request before recording remote attendance.</p>
                        @endif
                    </div>
                    @if ($todayAttendance?->time_in)
                        <span class="inline-flex w-fit rounded-full px-2.5 py-1 text-xs font-semibold {{ $todayAttendance->status === 'late' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">{{ ucfirst($todayAttendance->status) }}</span>
                    @endif
                </div>

                <div class="grid gap-3 p-6 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Time in</p><p class="mt-2 text-xl font-bold text-slate-900">{{ $todayAttendance?->formattedTimeIn() ?? '—' }}</p><p class="mt-1 text-xs text-slate-500">Regular time out: {{ $todayAttendance?->formattedTimeOut() ?? $formatScheduleTime($timeOut) }}</p></div>
                    <div class="attendance-time-card-paid rounded-xl border border-emerald-100 bg-emerald-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Paid work time</p><p class="mt-2 text-xl font-bold text-slate-900">{{ $todayAttendance?->workedDuration() ?? ($todayAttendance?->time_in ? 'In progress' : '—') }}</p><p class="mt-1 text-xs text-emerald-700">Fixed 12:00 PM–1:00 PM is excluded; completed OT is included</p></div>
                </div>

                @if ($todayAttendance?->time_in)
                    <details class="border-t border-slate-200 px-6 py-5">
                        <summary class="cursor-pointer font-semibold text-slate-700">View today’s time log</summary>
                        <div class="mt-4 flex items-center justify-between gap-4">
                            <p class="text-sm text-slate-500">Each attendance step is recorded at the time you complete it.</p>
                            @if ($todayAttendance->early_out)<span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Early departure</span>@endif
                        </div>
                        <ol class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            <li class="rounded-lg border border-slate-200 bg-slate-50 p-3"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Time in</p><p class="mt-1 font-semibold text-slate-900">{{ $todayAttendance->formattedTimeIn() }}</p></li>
                            @if ($todayAttendance->time_out)<li class="rounded-lg border border-slate-200 bg-slate-50 p-3"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $todayAttendance->early_out ? 'Early time out' : 'Regular time out' }}</p><p class="mt-1 font-semibold text-slate-900">{{ $todayAttendance->formattedTimeOut() }}</p></li>@endif
                            @if ($todayAttendance->overtime_in)<li class="rounded-lg border border-blue-100 bg-blue-50 p-3"><p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Overtime start</p><p class="mt-1 font-semibold text-slate-900">{{ $todayAttendance->formattedOvertimeIn() }}</p></li>@endif
                            @if ($todayAttendance->overtime_out)<li class="rounded-lg border border-blue-100 bg-blue-50 p-3"><p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Overtime end</p><p class="mt-1 font-semibold text-slate-900">{{ $todayAttendance->formattedOvertimeOut() }}</p></li>@endif
                        </ol>
                    </details>
                @endif

                <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-5 sm:flex-row sm:flex-wrap">
                    @if ($todayAttendance?->time_in && ! $todayAttendance->time_out)
                        @if ($canEarlyClockOut)
                            <form method="POST" action="{{ route('attendance.time-out-early') }}">@csrf @method('PATCH')<button type="submit" class="inline-flex w-full justify-center rounded-lg border border-amber-300 bg-amber-50 px-5 py-2.5 text-sm font-semibold text-amber-800 shadow-sm transition hover:bg-amber-100 sm:w-auto">Early clock out</button></form>
                        @endif
                    @endif
                    @if (! $todayAttendance?->time_in && $activeWfhRequest)
                        <form method="POST" action="{{ route('attendance.time-in') }}">@csrf<button type="submit" class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 sm:w-auto">Time in now</button></form>
                    @elseif (! $todayAttendance?->time_in)
                        <a href="{{ route('wfh.requests.create') }}" class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 sm:w-auto">Request WFH schedule</a>
                    @elseif (! $todayAttendance->time_out)
                        <form method="POST" action="{{ route('attendance.time-out') }}">@csrf @method('PATCH')<button type="submit" class="inline-flex w-full justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:w-auto">Clock out regular work</button></form>
                        <p class="self-center text-xs text-slate-500">Early clock out is available any time before {{ $formatScheduleTime($timeOut) }}. Regular time out is available from {{ $formatScheduleTime($timeOut) }}.</p>
                    @elseif (! $todayAttendance->overtime_in && $overtimeEligible)
                        <form method="POST" action="{{ route('attendance.overtime.start') }}">@csrf @method('PATCH')<button type="submit" class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 sm:w-auto">Start overtime</button></form>
                    @elseif ($isOvertimeActive)
                        <form method="POST" action="{{ route('attendance.overtime.end') }}">@csrf @method('PATCH')<button type="submit" class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 sm:w-auto">End overtime</button></form>
                    @else
                        <div class="inline-flex items-center rounded-lg bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700">{{ $todayAttendance?->early_out ? 'Early departure recorded. Overtime is unavailable for this workday.' : ($todayAttendance?->time_out && ! $overtimeEligible ? 'Regular workday complete. OT is unavailable because you clocked in after '.$formatScheduleTime($timeIn).'.' : 'Today’s attendance is complete.') }}</div>
                    @endif
                    <a href="{{ route('attendance.daily') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">View attendance history</a>
                </div>
            </article>

            <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <details>
                    <summary class="cursor-pointer text-sm font-semibold text-slate-700">Overtime rules</summary>
                    <p class="mt-3 text-sm leading-6 text-slate-600">To be eligible, clock in by {{ $formatScheduleTime($timeIn) }}, complete regular time out, then start overtime from {{ $formatScheduleTime($overtimeStart) }}.</p>
                </details>
            </aside>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4"><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Recent activity</p><h3 class="mt-1 text-lg font-bold text-slate-900">Latest attendance records</h3></div><a href="{{ route('attendance.daily') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Full history →</a></div>
            <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-3">Date</th><th class="px-6 py-3">Regular work</th><th class="px-6 py-3">Overtime</th><th class="px-6 py-3">Paid time</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Action</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse ($recentAttendance as $attendance)<tr class="text-slate-700"><td class="whitespace-nowrap px-6 py-4 font-medium text-slate-800">{{ $attendance->date->format('M j, Y') }}</td><td class="whitespace-nowrap px-6 py-4">{{ $attendance->formattedTimeIn() ?? '—' }} – {{ $attendance->formattedTimeOut() ?? '—' }}</td><td class="whitespace-nowrap px-6 py-4">{{ $attendance->formattedOvertimeIn() ?? '—' }} – {{ $attendance->formattedOvertimeOut() ?? '—' }}</td><td class="whitespace-nowrap px-6 py-4">{{ $attendance->workedDuration() ?? ($attendance->hasMissingClockOut() ? 'Pending verification' : 'In progress') }}</td><td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $attendance->hasMissingClockOut() || $attendance->status === 'late' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $attendance->hasMissingClockOut() ? 'Missing clock out' : ucfirst($attendance->status) }}</span></td><td class="px-6 py-4">@if ($attendance->hasMissingClockOut())<a href="{{ route('attendance.corrections', ['attendance_id' => $attendance->id]) }}" class="inline-flex whitespace-nowrap rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 transition hover:bg-amber-100">Correct now</a>@else<span class="text-slate-400">—</span>@endif</td></tr>@empty<tr><td colspan="6" class="px-6 py-10 text-center text-slate-500">No attendance records yet. Time in to start your first workday record.</td></tr>@endforelse</tbody></table></div>
        </section>
    </div>
</x-dashboard-layout>
