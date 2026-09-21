<x-dashboard-layout title="WFH Approvals">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Admin review</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">WFH approvals</h1>
                <p class="mt-2 text-sm text-slate-600">Review pending remote-work requests and record your decision.</p>
            </div>
            <a href="{{ route('wfh.my-requests') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">
                My WFH requests
            </a>
        </div>

        @if (session('approval_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('approval_success') }}
            </div>
        @endif

        @if (session('approval_error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                {{ session('approval_error') }}
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Awaiting review</h2>
                    <p class="mt-1 text-sm text-slate-500">Requests are shown oldest first.</p>
                </div>
                <span class="inline-flex min-w-10 items-center justify-center rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-800">
                    {{ $pendingRequests->count() }}
                </span>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse ($pendingRequests as $wfhRequest)
                    <article class="p-6">
                        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
                            <div>
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">
                                            {{ $wfhRequest->employee->first_name }} {{ $wfhRequest->employee->last_name }}
                                        </h3>
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $wfhRequest->employee->user->email }}
                                            @if ($wfhRequest->employee->department)
                                                <span class="px-1 text-slate-300">•</span>{{ $wfhRequest->employee->department->name }}
                                            @endif
                                        </p>
                                    </div>
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Pending</span>
                                </div>

                                <dl class="mt-5 grid gap-4 sm:grid-cols-3">
                                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Requested dates</dt>
                                        <dd class="mt-1 text-sm font-semibold text-slate-800">
                                            {{ $wfhRequest->date_from->format('M j, Y') }}
                                            @if (! $wfhRequest->date_from->isSameDay($wfhRequest->date_to))
                                                <span class="text-slate-400">–</span> {{ $wfhRequest->date_to->format('M j, Y') }}
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Work hours</dt>
                                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ date('g:i A', strtotime($wfhRequest->start_time)) }} – {{ date('g:i A', strtotime($wfhRequest->end_time)) }}</dd>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Arrangement</dt>
                                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $wfhRequest->request_type }}</dd>
                                    </div>
                                </dl>

                                <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Reason or purpose</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-700">{{ $wfhRequest->reason }}</p>
                                    @if ($wfhRequest->supporting_document)
                                        <a href="{{ asset('storage/'.$wfhRequest->supporting_document) }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-900">View supporting document</a>
                                    @endif
                                </div>
                            </div>

                            <form method="POST" action="{{ route('wfh.requests.approval', $wfhRequest) }}" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                @csrf
                                @method('PATCH')
                                <label for="remarks-{{ $wfhRequest->id }}" class="text-sm font-semibold text-slate-800">Decision note <span class="font-normal text-slate-400">(optional)</span></label>
                                <textarea id="remarks-{{ $wfhRequest->id }}" name="remarks" rows="5" maxlength="1000" class="mt-2 w-full rounded-lg border-slate-300 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Add a note for the employee.">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <button type="submit" name="status" value="rejected" class="rounded-lg border border-rose-200 bg-white px-3 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Not approve</button>
                                    <button type="submit" name="status" value="approved" class="rounded-lg bg-blue-600 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Approve</button>
                                </div>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="px-6 py-14 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-xl font-bold text-emerald-700">✓</div>
                        <h3 class="mt-4 text-base font-bold text-slate-900">All caught up</h3>
                        <p class="mt-1 text-sm text-slate-500">There are no WFH requests waiting for review.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-bold text-slate-900">Recent decisions</h2>
                <p class="mt-1 text-sm text-slate-500">The eight most recently reviewed WFH requests.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[42rem] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Employee</th>
                            <th class="px-6 py-3">Requested dates</th>
                            <th class="px-6 py-3">Decision</th>
                            <th class="px-6 py-3">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentDecisions as $wfhRequest)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $wfhRequest->employee->first_name }} {{ $wfhRequest->employee->last_name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $wfhRequest->date_from->format('M j') }} – {{ $wfhRequest->date_to->format('M j, Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $wfhRequest->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ ucfirst($wfhRequest->status) }}
                                    </span>
                                </td>
                                <td class="max-w-sm truncate px-6 py-4 text-slate-600">{{ $wfhRequest->remarks ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-500">No decisions have been recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-dashboard-layout>
