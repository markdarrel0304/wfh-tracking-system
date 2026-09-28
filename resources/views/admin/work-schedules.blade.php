<x-dashboard-layout title="Work Schedules Management">
    @php
        $weekdays = [
            'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 'thursday' => 'Thu',
            'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun',
        ];
    @endphp

    <div class="mx-auto max-w-6xl space-y-6 pb-6">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Administration</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Work schedules</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">Create schedule templates first, then open one to review its details and assign employees.</p>
            </div>
            <a href="{{ route('admin.work-schedules.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Create schedule</a>
        </section>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Schedule templates</h2>
                    <p class="mt-1 text-sm text-slate-600">Open a template only when you need to edit it or assign employees.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $schedules->count() }} schedules</span>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse ($schedules as $schedule)
                    <a href="{{ route('admin.work-schedules.show', $schedule) }}" class="flex items-center justify-between gap-4 px-6 py-5 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-600">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900">{{ $schedule->name }}</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ collect($schedule->days_json ?? [])->map(fn (string $day): string => $weekdays[$day] ?? ucfirst($day))->join(' · ') ?: 'No working days set' }}
                                <span class="px-1 text-slate-300">|</span>
                                {{ \Carbon\Carbon::parse($schedule->time_in)->format('g:i A') }}–{{ \Carbon\Carbon::parse($schedule->time_out)->format('g:i A') }}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="hidden rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 sm:inline">{{ $schedule->employees_count }} assigned</span>
                            <span class="text-sm font-semibold text-blue-600">View details →</span>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-14 text-center">
                        <p class="font-semibold text-slate-800">No schedule templates yet</p>
                        <p class="mt-1 text-sm text-slate-500">Create a template before assigning workdays and hours to employees.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-dashboard-layout>
