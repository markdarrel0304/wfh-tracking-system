<x-dashboard-layout title="Audit Logs">
    <div class="mx-auto max-w-7xl space-y-6 pb-6">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">System activity</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Audit logs</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">Review a permanent history of submitted requests, decisions, and reviewer actions.</p>
            </div>
            <p class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-600">Times use the system timezone.</p>
        </section>

        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-medium text-slate-500">All recorded activity</p><p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $totalLogs }}</p></div>
            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm"><p class="text-sm font-medium text-blue-700">This week</p><p class="mt-2 text-3xl font-bold tracking-tight text-blue-900">{{ $weeklyLogs }}</p></div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm"><p class="text-sm font-medium text-emerald-700">Review actions</p><p class="mt-2 text-3xl font-bold tracking-tight text-emerald-900">{{ $reviewLogs }}</p></div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('audit-logs.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_13rem_11rem_11rem_auto] xl:items-end">
                <div>
                    <label for="search" class="text-sm font-semibold text-slate-800">Search</label>
                    <input id="search" name="search" value="{{ request('search') }}" type="search" placeholder="Search actor or activity" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="event" class="text-sm font-semibold text-slate-800">Activity</label>
                    <select id="event" name="event" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All activity</option>
                        @foreach ($eventLabels as $event => $label)
                            <option value="{{ $event }}" @selected(request('event') === $event)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="from" class="text-sm font-semibold text-slate-800">From</label>
                    <input id="from" name="from" value="{{ request('from') }}" type="date" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="to" class="text-sm font-semibold text-slate-800">To</label>
                    <input id="to" name="to" value="{{ request('to') }}" type="date" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Filter</button>
                    <a href="{{ route('audit-logs.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Clear</a>
                </div>
            </form>
            @if ($errors->any())<p class="mt-3 text-sm text-rose-600">Please enter a valid filter range.</p>@endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-bold text-slate-900">Activity history</h2><p class="mt-1 text-sm text-slate-500">Open an entry to view the related record details, network address, and browser information.</p></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[64rem] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-3">Date & time</th><th class="px-6 py-3">Actor</th><th class="px-6 py-3">Activity</th><th class="px-6 py-3">Related record</th><th class="px-6 py-3">Details</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($logs as $log)
                            <tr class="align-top">
                                <td class="whitespace-nowrap px-6 py-4 text-slate-600"><p class="font-semibold text-slate-800">{{ $log->created_at->format('M j, Y') }}</p><p class="mt-1 text-xs">{{ $log->created_at->format('g:i:s A') }}</p></td>
                                <td class="px-6 py-4"><p class="font-semibold text-slate-800">{{ $log->actor?->name ?? 'Deleted user' }}</p><p class="mt-1 text-xs text-slate-500">{{ $log->actor?->email ?? 'No email available' }}</p></td>
                                <td class="px-6 py-4"><span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $eventLabels[$log->event] ?? $log->event }}</span><p class="mt-2 max-w-xs leading-5 text-slate-600">{{ $log->summary }}</p></td>
                                <td class="px-6 py-4 text-slate-700"><p class="font-semibold">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</p><p class="mt-1 text-xs text-slate-500">{{ $log->ip_address ?? 'IP unavailable' }}</p></td>
                                <td class="px-6 py-4">
                                    <details class="w-64 rounded-lg border border-slate-200 bg-slate-50">
                                        <summary class="cursor-pointer px-3 py-2 text-xs font-semibold text-slate-700">View full details</summary>
                                        <div class="border-t border-slate-200 px-3 py-3">
                                            <dl class="space-y-2 text-xs">
                                                @foreach ($log->metadata ?? [] as $key => $value)
                                                    <div class="grid grid-cols-[8rem_minmax(0,1fr)] gap-2"><dt class="font-semibold capitalize text-slate-500">{{ str_replace('_', ' ', $key) }}</dt><dd class="break-words text-slate-700">@if (is_bool($value)){{ $value ? 'Yes' : 'No' }}@elseif (is_array($value)){{ implode(', ', $value) }}@else{{ $value ?? '—' }}@endif</dd></div>
                                                @endforeach
                                                <div class="grid grid-cols-[8rem_minmax(0,1fr)] gap-2"><dt class="font-semibold text-slate-500">Browser</dt><dd class="break-words text-slate-700">{{ $log->user_agent ?: 'Unavailable' }}</dd></div>
                                            </dl>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-16 text-center"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-500">⌕</div><h3 class="mt-4 font-bold text-slate-900">No audit records found</h3><p class="mt-1 text-sm text-slate-500">Try clearing the filters or perform a tracked action.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())<div class="border-t border-slate-200 px-6 py-4">{{ $logs->links() }}</div>@endif
        </section>
    </div>
</x-dashboard-layout>
