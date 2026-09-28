<x-dashboard-layout title="Attendance Correction Approvals">
    <div class="mx-auto max-w-6xl space-y-6 pb-6">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Admin review</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Attendance correction approvals</h1>
                <p class="mt-2 text-sm text-slate-600">Open a request to review the details and make a decision.</p>
            </div>
            <a href="{{ route('wfh.approval') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">WFH approvals</a>
        </section>

        @if (session('approval_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('approval_success') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Awaiting review</h2>
                    <p class="mt-1 text-sm text-slate-500">Pending requests are shown oldest first.</p>
                </div>
                <span class="inline-flex min-w-10 items-center justify-center rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-800">{{ $pendingCorrections->count() }}</span>
            </div>

            @if ($pendingCorrections->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[34rem] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Employee</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($pendingCorrections as $correction)
                                <tr>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-slate-900">{{ $correction->employee->first_name }} {{ $correction->employee->last_name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Attendance correction request</p>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('approvals.attendance-corrections.review', $correction) }}" class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">View request</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-14 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-xl font-bold text-emerald-700">✓</div>
                    <h3 class="mt-4 text-base font-bold text-slate-900">All caught up</h3>
                    <p class="mt-1 text-sm text-slate-500">There are no attendance corrections waiting for review.</p>
                </div>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-bold text-slate-900">Recent decisions</h2>
                <p class="mt-1 text-sm text-slate-500">The eight most recently reviewed attendance corrections.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[42rem] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr><th class="px-6 py-3">Employee</th><th class="px-6 py-3">Workday</th><th class="px-6 py-3">Decision</th><th class="px-6 py-3">Review note</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentDecisions as $correction)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $correction->employee->first_name }} {{ $correction->employee->last_name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $correction->attendance->date->format('M j, Y') }}</td>
                                <td class="px-6 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $correction->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ ucfirst($correction->status) }}</span></td>
                                <td class="max-w-sm truncate px-6 py-4 text-slate-600">{{ $correction->remarks ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-10 text-center text-slate-500">No decisions have been recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-dashboard-layout>
