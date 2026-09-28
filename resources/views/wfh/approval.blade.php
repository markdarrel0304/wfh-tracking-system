<x-dashboard-layout title="WFH Approvals">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Admin review</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">WFH approvals</h1>
                <p class="mt-2 text-sm text-slate-600">Open a request to review its details and record a decision.</p>
            </div>
            <a href="{{ route('wfh.my-requests') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">My WFH requests</a>
        </div>

        @if (session('approval_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('approval_success') }}</div>
        @endif

        @if (session('approval_error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">{{ session('approval_error') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Awaiting review</h2>
                    <p class="mt-1 text-sm text-slate-500">Requests are shown oldest first.</p>
                </div>
                <span class="inline-flex min-w-10 items-center justify-center rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-800">{{ $pendingRequests->count() }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[52rem] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Employee</th>
                            <th class="px-6 py-3">Requested dates</th>
                            <th class="px-6 py-3">Schedule</th>
                            <th class="px-6 py-3">Arrangement</th>
                            <th class="px-6 py-3">Submitted</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pendingRequests as $wfhRequest)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4"><p class="font-semibold text-slate-900">{{ $wfhRequest->employee->first_name }} {{ $wfhRequest->employee->last_name }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $wfhRequest->employee->department?->name ?? 'No department' }}</p></td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-700">{{ $wfhRequest->date_from->format('M j, Y') }}@if (! $wfhRequest->date_from->isSameDay($wfhRequest->date_to)) <span class="text-slate-400">–</span> {{ $wfhRequest->date_to->format('M j, Y') }}@endif</td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-700">{{ date('g:i A', strtotime($wfhRequest->start_time)) }} – {{ date('g:i A', strtotime($wfhRequest->end_time)) }}</td>
                                <td class="px-6 py-4 text-slate-700">{{ $wfhRequest->request_type }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $wfhRequest->created_at->format('M j, g:i A') }}</td>
                                <td class="px-6 py-4 text-right"><a href="{{ route('wfh.approval.review', $wfhRequest) }}" class="inline-flex rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">Review request</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-14 text-center"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-xl font-bold text-emerald-700">✓</div><h3 class="mt-4 text-base font-bold text-slate-900">All caught up</h3><p class="mt-1 text-sm text-slate-500">There are no WFH requests waiting for review.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-bold text-slate-900">Recent decisions</h2><p class="mt-1 text-sm text-slate-500">The eight most recently reviewed WFH requests.</p></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[42rem] text-left text-sm"><thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-3">Employee</th><th class="px-6 py-3">Requested dates</th><th class="px-6 py-3">Decision</th><th class="px-6 py-3">Note</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse ($recentDecisions as $wfhRequest)<tr><td class="px-6 py-4 font-semibold text-slate-800">{{ $wfhRequest->employee->first_name }} {{ $wfhRequest->employee->last_name }}</td><td class="px-6 py-4 text-slate-600">{{ $wfhRequest->date_from->format('M j') }} – {{ $wfhRequest->date_to->format('M j, Y') }}</td><td class="px-6 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $wfhRequest->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ ucfirst($wfhRequest->status) }}</span></td><td class="max-w-sm truncate px-6 py-4 text-slate-600">{{ $wfhRequest->remarks ?: '—' }}</td></tr>@empty<tr><td colspan="4" class="px-6 py-10 text-center text-slate-500">No decisions have been recorded yet.</td></tr>@endforelse</tbody></table></div>
        </section>
    </div>
</x-dashboard-layout>
