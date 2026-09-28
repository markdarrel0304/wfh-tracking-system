@php
    use Carbon\Carbon;

    $reportMonths = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
    $reportYears = range(now()->subYears(5)->year, now()->addYear()->year);
    $reportCalendarDays = function (string $selectedDate): array {
        $calendarMonth = Carbon::parse($selectedDate)->startOfMonth();
        $firstVisibleDate = $calendarMonth->copy()->subDays($calendarMonth->dayOfWeek);
        $today = now()->toDateString();
        $days = [];

        for ($index = 0; $index < 42; $index++) {
            $date = $firstVisibleDate->copy()->addDays($index);
            $days[] = [
                'day' => $date->day,
                'date' => $date->toDateString(),
                'isCurrentMonth' => $date->month === $calendarMonth->month,
                'isSelected' => $date->toDateString() === $selectedDate,
                'isToday' => $date->toDateString() === $today,
            ];
        }

        return $days;
    };
@endphp

<x-dashboard-layout title="WFH Reports">
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                @if ($selectedEmployee)
                    <a href="{{ route('reports.wfh', $filters) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">← Back to employees</a>
                    <p class="mt-4 text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Employee WFH history</p>
                    <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">{{ $selectedEmployee->first_name }} {{ $selectedEmployee->last_name }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ $selectedEmployee->employee_number }} · {{ $selectedEmployee->department?->name ?? 'No department' }}</p>
                @else
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Reporting</p>
                    <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">WFH report</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Select an employee to review their remote-work requests, approval decisions, supporting documents, and weekly WFH usage.</p>
                @endif
            </div>
            <a href="{{ route('reports.wfh.export', array_merge(request()->query(), $selectedEmployee ? ['employee_id' => $selectedEmployee->id] : [])) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5v-9m0 9l-3.75-3.75M12 16.5l3.75-3.75M3.75 17.25v1.125c0 .932.756 1.688 1.688 1.688h13.125c.932 0 1.688-.756 1.688-1.688v-1.125" /></svg>
                Export CSV
            </a>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ $selectedEmployee ? route('reports.wfh.employee', $selectedEmployee) : route('reports.wfh') }}" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <label class="block"><span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">From</span><x-wfh-date-picker name="from" selectedDate="{{ $filters['from'] }}" title="Select start date" :showQuickSelect="false" :required="true" route="{{ route('reports.wfh') }}" year="{{ Carbon::parse($filters['from'])->year }}" month="{{ Carbon::parse($filters['from'])->month }}" :years="$reportYears" :months="$reportMonths" :calendarDays="$reportCalendarDays($filters['from'])" /></label>
                <label class="block"><span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">To</span><x-wfh-date-picker name="to" selectedDate="{{ $filters['to'] }}" title="Select end date" :showQuickSelect="false" :required="true" route="{{ route('reports.wfh') }}" year="{{ Carbon::parse($filters['to'])->year }}" month="{{ Carbon::parse($filters['to'])->month }}" :years="$reportYears" :months="$reportMonths" :calendarDays="$reportCalendarDays($filters['to'])" /></label>
                <label class="block"><span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Department</span><select name="department_id" class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500"><option value="">All departments</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected($filters['department_id'] === $department->id)>{{ $department->name }}</option>@endforeach</select></label>
                <div class="flex items-end gap-2"><label class="block min-w-0 flex-1"><span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</span><select name="status" class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500"><option value="">All statuses</option>@foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'] as $value => $label)<option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>@endforeach</select></label><button type="submit" class="shrink-0 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">Apply</button></div>
            </form>
            <div class="mt-3 flex justify-end"><a href="{{ $selectedEmployee ? route('reports.wfh.employee', $selectedEmployee) : route('reports.wfh') }}" class="text-sm font-semibold text-slate-500 hover:text-blue-600">Clear filters</a></div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-sm text-slate-500">WFH requests</p><p class="mt-2 text-2xl font-bold text-slate-950">{{ $summary['requests'] }}</p><p class="mt-1 text-xs text-slate-500">within selected period</p></div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4"><p class="text-sm text-emerald-700">Approved</p><p class="mt-2 text-2xl font-bold text-emerald-800">{{ $summary['approved'] }}</p><p class="mt-1 text-xs text-emerald-700">requests approved</p></div>
            <div class="rounded-xl border border-amber-100 bg-amber-50 p-4"><p class="text-sm text-amber-700">Pending</p><p class="mt-2 text-2xl font-bold text-amber-800">{{ $summary['pending'] }}</p><p class="mt-1 text-xs text-amber-700">awaiting a decision</p></div>
            <div class="rounded-xl border border-rose-100 bg-rose-50 p-4"><p class="text-sm text-rose-700">Not approved</p><p class="mt-2 text-2xl font-bold text-rose-800">{{ $summary['rejected'] }}</p><p class="mt-1 text-xs text-rose-700">requests declined</p></div>
            <div class="rounded-xl border border-blue-100 bg-blue-50 p-4"><p class="text-sm text-blue-700">Approved WFH days</p><p class="mt-2 text-2xl font-bold text-blue-800">{{ $summary['approved_days'] }}</p><p class="mt-1 text-xs text-blue-700">days in this period</p></div>
            <div class="rounded-xl border border-violet-100 bg-violet-50 p-4"><p class="text-sm text-violet-700">Weekly guideline</p><p class="mt-2 text-2xl font-bold text-violet-800">{{ $summary['guideline_weeks'] }}</p><p class="mt-1 text-xs text-violet-700">weeks above 3 WFH days</p></div>
        </section>

        @if ($selectedEmployee)
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><div><h3 class="font-semibold text-slate-950">WFH request history</h3><p class="mt-1 text-sm text-slate-500">Requests that overlap the selected reporting period.</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $wfhRequests->total() }} requests</span></div>
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-semibold">Requested dates</th><th class="px-5 py-3 font-semibold">Arrangement</th><th class="px-5 py-3 font-semibold">Hours</th><th class="px-5 py-3 font-semibold">Status</th><th class="px-5 py-3 font-semibold"><span class="sr-only">Details</span></th></tr></thead><tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($wfhRequests as $wfhRequest)
                        <tr class="align-top hover:bg-slate-50/80"><td class="whitespace-nowrap px-5 py-4 font-medium text-slate-900">{{ $wfhRequest->date_from?->format('M j, Y') }}<span class="text-slate-400"> – </span>{{ $wfhRequest->date_to?->format('M j, Y') }}</td><td class="px-5 py-4 text-slate-700">{{ $wfhRequest->request_type }}</td><td class="whitespace-nowrap px-5 py-4 text-slate-700">{{ $wfhRequest->start_time ? \Carbon\Carbon::parse($wfhRequest->start_time)->format('g:i A') : '—' }} – {{ $wfhRequest->end_time ? \Carbon\Carbon::parse($wfhRequest->end_time)->format('g:i A') : '—' }}</td><td class="px-5 py-4"><span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $wfhRequest->status === 'approved', 'bg-amber-50 text-amber-700' => $wfhRequest->status === 'pending', 'bg-rose-50 text-rose-700' => $wfhRequest->status === 'rejected', 'bg-slate-100 text-slate-700' => $wfhRequest->status === 'cancelled'])>{{ Str::headline($wfhRequest->status) }}</span></td><td class="px-5 py-4"><details><summary class="cursor-pointer whitespace-nowrap text-sm font-semibold text-blue-600 hover:text-blue-700">View details</summary><div class="mt-3 grid min-w-[28rem] grid-cols-2 gap-3 rounded-lg bg-slate-50 p-3 text-xs text-slate-600 sm:grid-cols-3"><div class="col-span-2 sm:col-span-3"><p class="uppercase tracking-wide text-slate-400">Employee reason</p><p class="mt-1 font-medium leading-5 text-slate-800">{{ $wfhRequest->reason }}</p></div><div><p class="uppercase tracking-wide text-slate-400">Submitted</p><p class="mt-1 font-semibold text-slate-800">{{ $wfhRequest->created_at?->format('M j, Y g:i A') }}</p></div><div><p class="uppercase tracking-wide text-slate-400">Decision date</p><p class="mt-1 font-semibold text-slate-800">{{ $wfhRequest->approved_at?->format('M j, Y g:i A') ?? '—' }}</p></div><div><p class="uppercase tracking-wide text-slate-400">Supporting proof</p><p class="mt-1 font-semibold text-slate-800">{{ $wfhRequest->supporting_document ? 'Attached' : 'None' }}</p></div>@if ($wfhRequest->remarks)<div class="col-span-2 sm:col-span-3"><p class="uppercase tracking-wide text-slate-400">Reviewer note</p><p class="mt-1 font-medium leading-5 text-slate-800">{{ $wfhRequest->remarks }}</p></div>@endif</div></details></td></tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-14 text-center"><p class="font-semibold text-slate-700">No WFH requests found</p><p class="mt-1 text-sm text-slate-500">Try changing the date range or status filter.</p></td></tr>
                    @endforelse
                </tbody></table></div>
                @if ($wfhRequests->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $wfhRequests->links() }}</div>@endif
            </section>
        @else
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><div><h3 class="font-semibold text-slate-950">Employees</h3><p class="mt-1 text-sm text-slate-500">Only employees with WFH requests matching the selected filters are listed.</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $employees->count() }} employees</span></div>
                <div class="divide-y divide-slate-100">
                    @forelse ($employees as $employee)
                        <div class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-semibold text-slate-900">{{ $employee->first_name }} {{ $employee->last_name }}</p><p class="mt-1 text-sm text-slate-500">{{ $employee->employee_number }} · {{ $employee->department?->name ?? 'No department' }}</p></div><a href="{{ route('reports.wfh.employee', array_merge(['employee' => $employee], $filters)) }}" class="inline-flex shrink-0 items-center justify-center rounded-lg border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-600 transition hover:border-blue-300 hover:bg-blue-50">View WFH history</a></div>
                    @empty
                        <div class="px-5 py-14 text-center"><p class="font-semibold text-slate-700">No employees found</p><p class="mt-1 text-sm text-slate-500">Try changing the report filters or date range.</p></div>
                    @endforelse
                </div>
            </section>
        @endif
    </div>
</x-dashboard-layout>
