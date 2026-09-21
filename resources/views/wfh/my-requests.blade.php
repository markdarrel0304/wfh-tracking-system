<x-dashboard-layout title="My WFH Requests">
    <div class="mx-auto max-w-6xl space-y-6 pb-8">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Remote work</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">My WFH requests</h1>
                <p class="mt-2 text-sm text-slate-600">Track the status of your work-from-home requests in one place.</p>
            </div>
            <a href="{{ route('wfh.requests.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">New WFH request</a>
        </header>

        <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">All requests</p><p class="mt-2 text-3xl font-bold text-slate-900">{{ $total }}</p><p class="mt-1 text-xs text-slate-500">requests submitted</p></div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-5"><p class="text-sm text-emerald-700">Approved</p><p class="mt-2 text-3xl font-bold text-emerald-700">{{ $approved }}</p><p class="mt-1 text-xs text-emerald-600">ready to work remotely</p></div>
            <div class="rounded-xl border border-amber-100 bg-amber-50 p-5"><p class="text-sm text-amber-700">Pending</p><p class="mt-2 text-3xl font-bold text-amber-700">{{ $pending }}</p><p class="mt-1 text-xs text-amber-600">awaiting review</p></div>
            <div class="rounded-xl border border-red-100 bg-red-50 p-5"><p class="text-sm text-red-700">Not approved</p><p class="mt-2 text-3xl font-bold text-red-700">{{ $rejected }}</p><p class="mt-1 text-xs text-red-600">requests to review</p></div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div><h2 class="font-semibold text-slate-900">Request history</h2><p class="mt-1 text-sm text-slate-500">Your submitted remote-work requests.</p></div>
                @if ($requests->isNotEmpty())
                    <div class="relative w-full sm:w-72"><input id="requestSearch" type="search" placeholder="Search requests" class="block w-full rounded-lg border-slate-300 py-2.5 pl-3 pr-10 text-sm focus:border-blue-500 focus:ring-blue-500"><span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">⌕</span></div>
                @endif
            </div>

            @if ($requests->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px]">
                        <thead class="border-b border-slate-200 bg-slate-50"><tr><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Requested dates</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Work hours</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Arrangement</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Reason</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th><th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th></tr></thead>
                        <tbody id="requestRows" class="divide-y divide-slate-100">
                            @foreach ($requests as $request)
                                @php $statusColors = ['pending' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-emerald-100 text-emerald-700', 'rejected' => 'bg-red-100 text-red-700']; @endphp
                                <tr class="request-row transition hover:bg-slate-50">
                                    <td class="px-6 py-4"><p class="font-medium text-slate-800">{{ $request->date_from->format('M d, Y') }}</p>@if ($request->date_to && ! $request->date_to->isSameDay($request->date_from))<p class="mt-1 text-sm text-slate-500">to {{ $request->date_to->format('M d, Y') }}</p>@endif</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $request->start_time ? date('g:i A', strtotime($request->start_time)) : '—' }} – {{ $request->end_time ? date('g:i A', strtotime($request->end_time)) : '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700">{{ $request->request_type }}</td>
                                    <td class="max-w-xs px-6 py-4 text-sm text-slate-600"><p class="truncate">{{ $request->reason }}</p></td>
                                    <td class="px-6 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusColors[$request->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($request->status) }}</span></td>
                                    <td class="px-6 py-4 text-right"><a href="{{ route('wfh.requests.show', $request) }}" class="inline-flex rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">View details</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="noSearchResults" class="hidden px-6 py-12 text-center text-sm text-slate-500">No requests match your search.</div>
            @else
                <div class="flex flex-col items-center px-6 py-16 text-center sm:py-20">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-2xl font-semibold text-blue-600">+</div>
                    <h3 class="mt-5 text-lg font-semibold text-slate-900">Your request history is empty</h3>
                    <p class="mt-2 max-w-sm text-sm leading-6 text-slate-500">Create your first work-from-home request and use this page to follow its approval status.</p>
                    <a href="{{ route('wfh.requests.create') }}" class="mt-6 inline-flex rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Create a WFH request</a>
                </div>
            @endif
        </section>
    </div>

    @if ($requests->isNotEmpty())
        @push('scripts')
        <script>
            document.getElementById('requestSearch')?.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('.request-row');
                let visibleRows = 0;

                rows.forEach(function (row) {
                    const matches = row.textContent.toLowerCase().includes(query);
                    row.classList.toggle('hidden', !matches);
                    visibleRows += matches ? 1 : 0;
                });

                document.getElementById('noSearchResults').classList.toggle('hidden', visibleRows > 0);
            });
        </script>
        @endpush
    @endif
</x-dashboard-layout>
