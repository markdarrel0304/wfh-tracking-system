<x-dashboard-layout title="Attendance History">
    <div class="mx-auto max-w-6xl space-y-6 pb-6">
        <section class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white px-6 py-7 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Attendance</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Attendance history</h2>
                <p class="mt-2 text-sm text-slate-600">Review your recorded workdays, hours, and attendance status.</p>
            </div>
            <a href="{{ route('attendance.time-in-out') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Back to time in / out</a>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full table-fixed text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr><th class="px-4 py-4">Date</th><th class="px-4 py-4">Arrangement</th><th class="px-4 py-4">Regular work</th><th class="px-4 py-4">Overtime</th><th class="px-4 py-4">Paid duration</th><th class="w-36 whitespace-nowrap px-4 py-4">Status</th><th class="w-40 whitespace-nowrap px-4 py-4">Action</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($attendances as $attendance)
                            <tr class="text-slate-700">
                                <td class="whitespace-nowrap px-4 py-4 font-semibold text-slate-800">{{ $attendance->date->format('D, M j, Y') }}</td>
                                <td class="whitespace-nowrap px-4 py-4">{{ $attendance->wfhRequest ? 'Work From Home' : 'On-site' }}</td>
                                <td class="whitespace-nowrap px-4 py-4">{{ $attendance->formattedTimeIn() ?? '—' }} – {{ $attendance->formattedTimeOut() ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-4">{{ $attendance->formattedOvertimeIn() ?? '—' }} – {{ $attendance->formattedOvertimeOut() ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 font-medium">{{ $attendance->workedDuration() ?? ($attendance->hasMissingClockOut() ? 'Pending verification' : 'In progress') }}</td>
                                <td class="w-36 whitespace-nowrap px-4 py-4"><span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $attendance->hasMissingClockOut() || $attendance->early_out || $attendance->status === 'late' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $attendance->hasMissingClockOut() ? 'Missing clock out' : ($attendance->early_out ? 'Early out' : ucfirst($attendance->status)) }}</span></td>
                                <td class="w-40 whitespace-nowrap px-4 py-4">@if ($attendance->hasMissingClockOut())<a href="{{ route('attendance.corrections', ['attendance_id' => $attendance->id]) }}" class="inline-flex whitespace-nowrap rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 transition hover:bg-amber-100">Correct time out</a>@else<span class="text-slate-400">—</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-16 text-center"><p class="font-semibold text-slate-800">No attendance records yet</p><p class="mt-1 text-sm text-slate-500">Your completed workdays will appear here.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($attendances->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">{{ $attendances->links() }}</div>
            @endif
        </section>
    </div>
</x-dashboard-layout>
