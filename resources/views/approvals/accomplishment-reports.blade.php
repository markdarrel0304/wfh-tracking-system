<x-dashboard-layout title="Accomplishment Report Approvals">
    <div class="mx-auto max-w-6xl space-y-6 pb-6">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Admin review</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Accomplishment report approvals</h1>
                <p class="mt-2 text-sm text-slate-600">Review completed work and supporting files before closing each employee report.</p>
            </div>
            <a href="{{ route('approvals.wfh-requests') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">WFH approvals</a>
        </section>

        @if (session('approval_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('approval_success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-semibold">The report could not be reviewed.</p>
                <ul class="mt-1 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-amber-700">Awaiting review</p>
                <p class="mt-2 text-3xl font-bold text-amber-800">{{ $pendingReports->count() }}</p>
                <p class="mt-1 text-xs text-amber-700">submitted accomplishments</p>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-700">Reports reviewed</p>
                <p class="mt-2 text-3xl font-bold text-emerald-800">{{ $reviewedCount }}</p>
                <p class="mt-1 text-xs text-emerald-700">accomplishments reviewed</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Review process</p>
                <p class="mt-2 text-xl font-bold text-slate-900">One review per entry</p>
                <p class="mt-1 text-xs text-slate-500">Review the employee’s submitted accomplishment and attachments together.</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Accomplishments awaiting review</h2>
                    <p class="mt-1 text-sm text-slate-500">Each submission contains the employee’s update and any supporting files.</p>
                </div>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">{{ $pendingReports->count() }}</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($pendingReports as $report)
                    <article class="grid gap-4 px-6 py-5 lg:grid-cols-[minmax(11rem,0.8fr)_minmax(14rem,1.25fr)_auto_auto] lg:items-center lg:gap-x-8">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-bold text-slate-900">{{ $report->employee->first_name }} {{ $report->employee->last_name }}</h3>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $report->employee->employee_number }}</span>
                            </div>
                            <p class="mt-1 truncate text-sm text-slate-500">{{ $report->employee->department?->name ?? 'No department' }}</p>
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-semibold text-slate-800">{{ $report->title ?? $report->dailyTask?->title ?? 'Accomplishment' }}</p>
                                @if ($report->category)<span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ ucfirst($report->category) }}</span>@endif
                                @if ($report->progress_status)<span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ str($report->progress_status)->headline() }}</span>@endif
                            </div>
                        </div>

                        <div class="flex items-center gap-6 text-sm">
                            <span class="text-slate-600"><span class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Workday</span>{{ $report->date->format('M j, Y') }}</span>
                            <span class="text-slate-600"><span class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Files</span>{{ $report->outputAttachments->count() }}</span>
                        </div>
                        <div class="flex items-center gap-3 lg:justify-self-end">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $report->revision_requested_at ? 'bg-rose-50 text-rose-700' : 'bg-amber-100 text-amber-800' }}">{{ $report->revision_requested_at ? 'Changes requested' : 'Submitted' }}</span>
                            <button type="button" onclick="document.getElementById('report-details-{{ $report->id }}').showModal()" class="rounded-lg border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">View report</button>
                        </div>
                    </article>

                    <dialog id="report-details-{{ $report->id }}" class="max-h-[90vh] w-[min(58rem,calc(100%-2rem))] rounded-2xl border-0 bg-transparent p-0 shadow-2xl backdrop:bg-slate-950/40">
                        <div class="max-h-[90vh] overflow-y-auto rounded-2xl bg-white">
                            <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-200 bg-white px-6 py-5">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Accomplishment review</p>
                                    <h3 class="mt-1 text-xl font-bold text-slate-900">{{ $report->employee->first_name }} {{ $report->employee->last_name }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ $report->employee->employee_number }} · {{ $report->employee->department?->name ?? 'No department' }} · {{ $report->date->format('M j, Y') }}</p>
                                </div>
                                <form method="dialog"><button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Close</button></form>
                            </div>

                            <div class="space-y-5 p-6">
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Accomplishment</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $report->title ?? $report->dailyTask?->title ?? 'Accomplishment' }}</p>@if ($report->category)<p class="mt-1 text-xs text-slate-500">{{ ucfirst($report->category) }} · {{ str($report->progress_status ?? 'completed')->headline() }}</p>@endif</div>
                                    <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Workday</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $report->date->format('l, M j') }}</p></div>
                                    <div class="rounded-xl bg-blue-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Supporting files</p><p class="mt-2 text-sm font-semibold text-blue-900">{{ $report->outputAttachments->count() }} attachment{{ $report->outputAttachments->count() === 1 ? '' : 's' }}</p></div>
                                </div>

                                @if ($report->dailyTask?->task_description)
                                    <div class="rounded-xl border border-slate-200 p-4">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Task details</p>
                                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $report->dailyTask->task_description }}</p>
                                    </div>
                                @endif

                                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Employee accomplishment summary</p>
                                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $report->summary }}</p>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-xl border border-slate-200 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Blockers</p><p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $report->blockers ?: 'No blockers reported.' }}</p></div>
                                    <div class="rounded-xl border border-slate-200 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Next steps</p><p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $report->next_steps ?: 'No next steps reported.' }}</p></div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="flex items-center justify-between gap-3"><div><h4 class="font-semibold text-slate-900">Output attachments</h4><p class="mt-1 text-sm text-slate-500">Open files to validate the employee’s submitted work.</p></div><span class="text-xs font-semibold text-slate-500">{{ $report->outputAttachments->count() }} file{{ $report->outputAttachments->count() === 1 ? '' : 's' }}</span></div>
                                    @if ($report->outputAttachments->isNotEmpty())
                                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                            @foreach ($report->outputAttachments as $attachment)
                                                <a href="{{ route('accomplishments.attachments.download', $attachment) }}" class="flex min-w-0 items-center gap-3 rounded-lg bg-slate-50 p-3 transition hover:bg-blue-50">
                                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-xs font-bold uppercase text-blue-700">{{ pathinfo($attachment->file_name, PATHINFO_EXTENSION) ?: 'file' }}</span>
                                                    <span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold text-slate-800">{{ $attachment->file_name }}</span><span class="mt-1 block text-xs text-slate-500">{{ $attachment->size_bytes && $attachment->size_bytes >= 1048576 ? number_format($attachment->size_bytes / 1048576, 1).' MB' : number_format(($attachment->size_bytes ?? 0) / 1024, 1).' KB' }}</span><span class="mt-1 block truncate text-xs font-medium text-blue-700">{{ $attachment->dailyTask ? 'Task: '.$attachment->dailyTask->title : 'Supports the daily report' }}</span></span>
                                                    <span class="text-xs font-semibold text-blue-700">Download</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="mt-4 text-sm text-slate-500">No output files were attached to this report.</p>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('approvals.accomplishment-reports.update', $report) }}" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    @csrf
                                    @method('PATCH')
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                                        <div class="flex-1"><label for="review_note_{{ $report->id }}" class="text-sm font-semibold text-slate-800">Review note <span class="font-normal text-slate-400">(optional)</span></label><textarea id="review_note_{{ $report->id }}" name="review_note" rows="3" maxlength="1000" class="mt-2 block w-full resize-y rounded-lg border-slate-300 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500" placeholder="Add a note or feedback for the employee."></textarea></div>
                                        <div class="flex flex-col gap-2 sm:flex-row"><button type="submit" formaction="{{ route('approvals.accomplishment-reports.request-revision', $report) }}" class="inline-flex justify-center rounded-lg border border-amber-300 bg-white px-5 py-2.5 text-sm font-semibold text-amber-800 transition hover:bg-amber-50">Request changes</button><button type="submit" class="inline-flex justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Mark reviewed</button></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </dialog>
                @empty
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-xl font-bold text-emerald-700">✓</div>
                        <h3 class="mt-4 font-bold text-slate-900">All caught up</h3>
                        <p class="mt-1 text-sm text-slate-500">There are no accomplishment reports awaiting review.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-bold text-slate-900">Recent reviews</h2>
                <p class="mt-1 text-sm text-slate-500">The eight most recently reviewed accomplishment reports.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentReviews as $report)
                    <article class="grid gap-3 px-6 py-5 md:grid-cols-[minmax(0,1fr)_10rem_minmax(0,1fr)] md:items-start">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $report->employee->first_name }} {{ $report->employee->last_name }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $report->date->format('M j, Y') }} · Reviewed by {{ $report->reviewer?->first_name }} {{ $report->reviewer?->last_name }}</p>
                        </div>
                        <span class="w-fit rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Reviewed</span>
                        <p class="text-sm leading-6 text-slate-600">{{ $report->review_note ?: 'No review note added.' }}</p>
                    </article>
                @empty
                    <div class="px-6 py-14 text-center text-sm text-slate-500">No reports have been reviewed yet.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-dashboard-layout>
