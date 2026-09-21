@php
use Carbon\Carbon;

$previousMonth = $calendarMonth->copy()->subMonth();
$nextMonth = $calendarMonth->copy()->addMonth();
$leadingDays = $calendarMonth->dayOfWeek;
$daysInMonth = $calendarMonth->daysInMonth;
$trailingDays = 42 - ($leadingDays + $daysInMonth);
$today = Carbon::today();
$statusColors = [
    'pending' => 'border-amber-200 bg-amber-50 text-amber-800',
    'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
    'rejected' => 'border-red-200 bg-red-50 text-red-800',
];
@endphp

<x-dashboard-layout title="WFH Calendar">
    <div class="mx-auto max-w-6xl space-y-6 pb-8">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Remote work</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">WFH calendar</h1>
                <p class="mt-2 text-sm text-slate-600">See your submitted work-from-home requests across the month.</p>
            </div>
            <a href="{{ route('wfh.requests.create') }}" class="inline-flex justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">New WFH request</a>
        </header>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-8">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6">
                    <a href="{{ route('wfh.calendar', ['year' => $previousMonth->year, 'month' => $previousMonth->month]) }}" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100" aria-label="Previous month">‹</a>
                    <div class="text-center"><h2 class="font-semibold text-slate-900">{{ $calendarMonth->format('F Y') }}</h2><a href="{{ route('wfh.calendar') }}" class="mt-1 inline-block text-xs font-semibold text-blue-600 hover:text-blue-700">Jump to today</a></div>
                    <a href="{{ route('wfh.calendar', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100" aria-label="Next month">›</a>
                </div>

                <div class="p-3 sm:p-6">
                    <div class="grid grid-cols-7 gap-1 border-b border-slate-100 pb-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-400">
                        @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)<span class="py-2">{{ $dayName }}</span>@endforeach
                    </div>
                    <div class="grid grid-cols-7 gap-1 pt-2">
                        @for ($day = $leadingDays; $day > 0; $day--)<div class="min-h-20 rounded-lg bg-slate-50 p-2 text-xs text-slate-300 sm:min-h-24">{{ $previousMonth->daysInMonth - $day + 1 }}</div>@endfor
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $date = $calendarMonth->copy()->day($day);
                                $requestForDay = $requests->first(fn ($request) => $date->greaterThanOrEqualTo($request->date_from) && $date->lessThanOrEqualTo($request->date_to));
                            @endphp
                            <div class="min-h-20 rounded-lg border p-2 sm:min-h-24 {{ $date->isSameDay($today) ? 'border-blue-300 bg-blue-50' : 'border-slate-100 bg-white' }}">
                                <div class="flex items-center justify-between"><span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold {{ $date->isSameDay($today) ? 'bg-blue-600 text-white' : 'text-slate-700' }}">{{ $day }}</span>@if ($requestForDay)<span class="h-2 w-2 rounded-full {{ $requestForDay->status === 'approved' ? 'bg-emerald-500' : ($requestForDay->status === 'pending' ? 'bg-amber-500' : 'bg-red-500') }}"></span>@endif</div>
                                @if ($requestForDay)
                                    <a href="{{ route('wfh.requests.show', $requestForDay) }}" class="mt-2 block rounded-md border px-1.5 py-1 text-[10px] font-semibold leading-4 transition hover:opacity-75 sm:text-xs {{ $statusColors[$requestForDay->status] ?? 'border-slate-200 bg-slate-50 text-slate-700' }}">{{ ucfirst($requestForDay->status) }}<span class="hidden sm:inline"> WFH</span></a>
                                @endif
                            </div>
                        @endfor
                        @for ($day = 1; $day <= $trailingDays; $day++)<div class="min-h-20 rounded-lg bg-slate-50 p-2 text-xs text-slate-300 sm:min-h-24">{{ $day }}</div>@endfor
                    </div>
                </div>
            </section>

            <aside class="space-y-5 lg:col-span-4 lg:sticky lg:top-6">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-slate-900">This month</h2>
                    <dl class="mt-5 space-y-4 text-sm">
                        <div class="flex items-center justify-between"><dt class="text-slate-500">Requests</dt><dd class="text-lg font-bold text-slate-900">{{ $requests->count() }}</dd></div>
                        <div class="flex items-center justify-between"><dt class="text-slate-500">Approved</dt><dd class="font-semibold text-emerald-700">{{ $requests->where('status', 'approved')->count() }}</dd></div>
                        <div class="flex items-center justify-between"><dt class="text-slate-500">Pending</dt><dd class="font-semibold text-amber-700">{{ $requests->where('status', 'pending')->count() }}</dd></div>
                    </dl>
                </section>

                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-semibold text-slate-900">Requests this month</h2><p class="mt-1 text-sm text-slate-500">Select a request for more details.</p></div>
                    @if ($requests->isNotEmpty())
                        <div class="divide-y divide-slate-100">
                            @foreach ($requests as $request)
                                <a href="{{ route('wfh.requests.show', $request) }}" class="block px-5 py-4 transition hover:bg-slate-50"><div class="flex items-start justify-between gap-3"><div><p class="font-medium text-slate-800">{{ $request->date_from->format('M d') }}@if (! $request->date_to->isSameDay($request->date_from)) – {{ $request->date_to->format('M d') }}@endif</p><p class="mt-1 text-sm text-slate-500">{{ $request->request_type }}</p></div><span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusColors[$request->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($request->status) }}</span></div></a>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center"><p class="text-sm font-medium text-slate-700">No requests this month</p><p class="mt-2 text-sm leading-5 text-slate-500">Create a request to see it on your calendar.</p><a href="{{ route('wfh.requests.create') }}" class="mt-4 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-700">Create request</a></div>
                    @endif
                </section>

                <section class="rounded-xl border border-blue-200 bg-blue-50 p-5"><h2 class="font-semibold text-blue-900">Status guide</h2><div class="mt-4 space-y-3 text-sm"><p class="flex items-center gap-2 text-blue-900"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Approved</p><p class="flex items-center gap-2 text-blue-900"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>Pending review</p><p class="flex items-center gap-2 text-blue-900"><span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>Not approved</p></div></section>
            </aside>
        </div>
    </div>
</x-dashboard-layout>
