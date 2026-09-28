<x-dashboard-layout title="Attendance Reports">
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                @if ($selectedEmployee)
                    <a href="{{ route('reports.attendance', $filters) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">← Back to employees</a>
                    <p class="mt-4 text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Employee attendance</p>
                    <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">{{ $selectedEmployee->first_name }} {{ $selectedEmployee->last_name }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ $selectedEmployee->employee_number }} · {{ $selectedEmployee->department?->name ?? 'No department' }}</p>
                @else
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Reporting</p>
                    <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Attendance report</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Select an employee to review their full attendance history, including lunch, overtime, and departures.</p>
                @endif
            </div>
            <a href="{{ route('reports.attendance.export', array_merge(request()->query(), $selectedEmployee ? ['employee_id' => $selectedEmployee->id] : [])) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5v-9m0 9l-3.75-3.75M12 16.5l3.75-3.75M3.75 17.25v1.125c0 .932.756 1.688 1.688 1.688h13.125c.932 0 1.688-.756 1.688-1.688v-1.125" /></svg>
                Export CSV
            </a>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ $selectedEmployee ? route('reports.attendance.employee', $selectedEmployee) : route('reports.attendance') }}" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <label class="block"><span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">From</span><input type="date" name="from" value="{{ $filters['from'] }}" class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500"></label>
                <label class="block"><span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">To</span><input type="date" name="to" value="{{ $filters['to'] }}" class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500"></label>
                <label class="block"><span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Department</span><select name="department_id" class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500"><option value="">All departments</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected($filters['department_id'] === $department->id)>{{ $department->name }}</option>@endforeach</select></label>
                <div class="flex items-end gap-2"><label class="block min-w-0 flex-1"><span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</span><select name="status" class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500"><option value="">All statuses</option>@foreach (['present' => 'Present', 'late' => 'Late', 'absent' => 'Absent', 'on-leave' => 'On leave'] as $value => $label)<option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>@endforeach</select></label><button type="submit" class="shrink-0 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">Apply</button></div>
            </form>
            <div class="mt-3 flex justify-end"><a href="{{ $selectedEmployee ? route('reports.attendance.employee', $selectedEmployee) : route('reports.attendance') }}" class="text-sm font-semibold text-slate-500 hover:text-blue-600">Clear filters</a></div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-sm text-slate-500">Attendance records</p><p class="mt-2 text-2xl font-bold text-slate-950">{{ $summary['records'] }}</p><p class="mt-1 text-xs text-slate-500">within selected period</p></div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4"><p class="text-sm text-emerald-700">Completed workdays</p><p class="mt-2 text-2xl font-bold text-emerald-800">{{ $summary['completed'] }}</p><p class="mt-1 text-xs text-emerald-700">with a recorded time out</p></div>
            <div class="rounded-xl border border-amber-100 bg-amber-50 p-4"><p class="text-sm text-amber-700">Late arrivals</p><p class="mt-2 text-2xl font-bold text-amber-800">{{ $summary['late'] }}</p><p class="mt-1 text-xs text-amber-700">clock-ins marked late</p></div>
            <div class="rounded-xl border border-orange-100 bg-orange-50 p-4"><p class="text-sm text-orange-700">Early departures</p><p class="mt-2 text-2xl font-bold text-orange-800">{{ $summary['early_departures'] }}</p><p class="mt-1 text-xs text-orange-700">recorded before 5:00 PM</p></div>
            <div class="rounded-xl border border-blue-100 bg-blue-50 p-4"><p class="text-sm text-blue-700">Paid work time</p><p class="mt-2 text-2xl font-bold text-blue-800">{{ sprintf('%dh %02dm', intdiv($summary['paid_minutes'], 60), $summary['paid_minutes'] % 60) }}</p><p class="mt-1 text-xs text-blue-700">{{ sprintf('%dh %02dm', intdiv($summary['overtime_minutes'], 60), $summary['overtime_minutes'] % 60) }} overtime</p></div>
        </section>

        @if ($selectedEmployee)
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><div><h3 class="font-semibold text-slate-950">Attendance history</h3><p class="mt-1 text-sm text-slate-500">Every recorded workday for this employee in the selected period.</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $attendances->total() }} records</span></div>
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-semibold">Workday</th><th class="px-5 py-3 font-semibold">Regular work</th><th class="px-5 py-3 font-semibold">Paid time</th><th class="px-5 py-3 font-semibold">Status</th><th class="px-5 py-3 font-semibold"><span class="sr-only">Details</span></th></tr></thead><tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($attendances as $attendance)
                        <tr class="align-top hover:bg-slate-50/80"><td class="whitespace-nowrap px-5 py-4 font-medium text-slate-900">{{ $attendance->date?->format('M j, Y') }}</td><td class="whitespace-nowrap px-5 py-4 text-slate-700">{{ $attendance->formattedTimeIn() ?? '—' }} – {{ $attendance->formattedTimeOut() ?? '—' }}</td><td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-900">{{ $attendance->workedDuration() ?? 'In progress' }}</td><td class="px-5 py-4"><span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $attendance->status === 'present', 'bg-amber-50 text-amber-700' => $attendance->status === 'late', 'bg-rose-50 text-rose-700' => $attendance->status === 'absent', 'bg-blue-50 text-blue-700' => $attendance->status === 'on-leave'])>{{ Str::headline($attendance->status) }}</span></td><td class="px-5 py-4"><details><summary class="cursor-pointer whitespace-nowrap text-sm font-semibold text-blue-600 hover:text-blue-700">View details</summary><div class="mt-3 grid min-w-[27rem] grid-cols-2 gap-2 rounded-lg bg-slate-50 p-3 text-xs text-slate-600 sm:grid-cols-3"><div><p class="uppercase tracking-wide text-slate-400">Overtime</p><p class="mt-1 font-semibold text-slate-800">{{ $attendance->formattedOvertimeIn() ?? '—' }} – {{ $attendance->formattedOvertimeOut() ?? '—' }}</p></div><div><p class="uppercase tracking-wide text-slate-400">Arrangement</p><p class="mt-1 font-semibold text-slate-800">{{ $attendance->wfhRequest ? 'Work From Home' : 'Office / on-site' }}</p></div><div><p class="uppercase tracking-wide text-slate-400">Regular time</p><p class="mt-1 font-semibold text-slate-800">{{ $attendance->regularWorkedMinutes() === null ? '—' : sprintf('%dh %02dm', intdiv($attendance->regularWorkedMinutes(), 60), $attendance->regularWorkedMinutes() % 60) }}</p></div><div><p class="uppercase tracking-wide text-slate-400">Overtime total</p><p class="mt-1 font-semibold text-slate-800">{{ $attendance->overtimeMinutes() === null ? '—' : sprintf('%dh %02dm', intdiv($attendance->overtimeMinutes(), 60), $attendance->overtimeMinutes() % 60) }}</p></div><div><p class="uppercase tracking-wide text-slate-400">Departure</p><p class="mt-1 font-semibold text-slate-800">{{ $attendance->early_out ? 'Early departure' : 'Regular' }}</p></div></div></details></td></tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-14 text-center"><p class="font-semibold text-slate-700">No attendance records found</p><p class="mt-1 text-sm text-slate-500">Try changing the date range or status filter.</p></td></tr>
                    @endforelse
                </tbody></table></div>
                @if ($attendances->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $attendances->links() }}</div>@endif
            </section>
        @else
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><div><h3 class="font-semibold text-slate-950">Employees</h3><p class="mt-1 text-sm text-slate-500">Only employees with attendance records matching the selected filters are listed.</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $employees->count() }} employees</span></div>
                <div class="divide-y divide-slate-100">
                    @forelse ($employees as $employee)
                        <div class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-semibold text-slate-900">{{ $employee->first_name }} {{ $employee->last_name }}</p><p class="mt-1 text-sm text-slate-500">{{ $employee->employee_number }} · {{ $employee->department?->name ?? 'No department' }}</p></div><a href="{{ route('reports.attendance.employee', array_merge(['employee' => $employee], $filters)) }}" class="inline-flex shrink-0 items-center justify-center rounded-lg border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-600 transition hover:border-blue-300 hover:bg-blue-50">View attendance</a></div>
                    @empty
                        <div class="px-5 py-14 text-center"><p class="font-semibold text-slate-700">No employees found</p><p class="mt-1 text-sm text-slate-500">Try changing the report filters or date range.</p></div>
                    @endforelse
                </div>
            </section>
        @endif
    </div>
</x-dashboard-layout>
