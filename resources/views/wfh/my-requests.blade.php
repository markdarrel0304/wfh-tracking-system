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

        @if (session('success'))
            <section class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-900" role="status">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white" aria-hidden="true">✓</span>
                    <div>
                        <p class="font-semibold">Request sent successfully</p>
                        <p class="mt-1 text-sm text-emerald-800">{{ session('success') }}</p>
                    </div>
                </div>
            </section>
        @endif

        <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">All requests</p><p class="mt-2 text-3xl font-bold text-slate-900">{{ $total }}</p><p class="mt-1 text-xs text-slate-500">requests submitted</p></div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-5"><p class="text-sm text-emerald-700">Approved</p><p class="mt-2 text-3xl font-bold text-emerald-700">{{ $approved }}</p><p class="mt-1 text-xs text-emerald-600">ready to work remotely</p></div>
            <div class="rounded-xl border border-amber-100 bg-amber-50 p-5"><p class="text-sm text-amber-700">Pending</p><p class="mt-2 text-3xl font-bold text-amber-700">{{ $pending }}</p><p class="mt-1 text-xs text-amber-600">awaiting review</p></div>
            <div class="rounded-xl border border-red-100 bg-red-50 p-5"><p class="text-sm text-red-700">Not approved</p><p class="mt-2 text-3xl font-bold text-red-700">{{ $rejected }}</p><p class="mt-1 text-xs text-red-600">requests to review</p></div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div><h2 class="font-semibold text-slate-900">Request history</h2><p class="mt-1 text-sm text-slate-500">Your submitted remote-work requests.</p></div>
                <div class="relative w-full sm:w-72"><input id="requestSearch" type="search" placeholder="Search requests" class="block w-full rounded-lg border-slate-300 py-2.5 pl-3 pr-10 text-sm focus:border-blue-500 focus:ring-blue-500"><span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">⌕</span></div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px]">
                    <thead class="border-b border-slate-200 bg-slate-50"><tr><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Requested dates</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Work hours</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Arrangement</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Reason</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th><th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th></tr></thead>
                    <tbody id="requestRows" class="divide-y divide-slate-100">
                        @forelse ($requests as $wfhRequest)
                            <tr class="request-row transition hover:bg-slate-50">
                                <td class="px-6 py-4"><p class="font-medium text-slate-800">{{ $wfhRequest->date_from->format('M d, Y') }}{{ $wfhRequest->date_to && ! $wfhRequest->date_to->isSameDay($wfhRequest->date_from) ? ' to '.$wfhRequest->date_to->format('M d, Y') : '' }}</p></td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $wfhRequest->start_time ? date('g:i A', strtotime($wfhRequest->start_time)) : '—' }} – {{ $wfhRequest->end_time ? date('g:i A', strtotime($wfhRequest->end_time)) : '—' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $wfhRequest->request_type }}</td>
                                <td class="max-w-xs px-6 py-4 text-sm text-slate-600"><p class="truncate">{{ $wfhRequest->reason }}</p></td>
                                <td class="px-6 py-4"><span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-amber-100 text-amber-700' => $wfhRequest->status === 'pending', 'bg-emerald-100 text-emerald-700' => $wfhRequest->status === 'approved', 'bg-red-100 text-red-700' => $wfhRequest->status === 'rejected', 'bg-slate-100 text-slate-700' => ! in_array($wfhRequest->status, ['pending', 'approved', 'rejected'], true)])>{{ ucfirst($wfhRequest->status) }}</span></td>
                                <td class="px-6 py-4 text-right"><a href="{{ route('wfh.requests.show', $wfhRequest) }}" class="inline-flex rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">View details</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-16 text-center"><p class="text-lg font-semibold text-slate-900">Your request history is empty</p><p class="mt-2 text-sm text-slate-500">Create your first work-from-home request to follow its approval status here.</p><a href="{{ route('wfh.requests.create') }}" class="mt-5 inline-flex rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Create a WFH request</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="noSearchResults" class="hidden px-6 py-12 text-center text-sm text-slate-500">No requests match your search.</div>
        </section>
    </div>

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
</x-dashboard-layout>
