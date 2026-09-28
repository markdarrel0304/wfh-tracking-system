<x-dashboard-layout title="Accomplishment Reports">
    @php
        $isEmployeeDetail = $selectedEmployee !== null;
        $currentRoute = $isEmployeeDetail ? route('reports.accomplishments.employee', $selectedEmployee) : route('reports.accomplishments');
        $exportParameters = array_filter(array_merge($filters, $isEmployeeDetail ? ['employee_id' => $selectedEmployee->id] : []));
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                @if ($isEmployeeDetail)
                    <a href="{{ route('reports.accomplishments', request()->only('from', 'to', 'department_id', 'status')) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">← Back to employees</a>
                    <p class="mt-5 text-xs font-bold tracking-[0.18em] text-blue-600">EMPLOYEE ACCOMPLISHMENT HISTORY</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">{{ trim($selectedEmployee->first_name.' '.$selectedEmployee->last_name) }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ $selectedEmployee->employee_number }} · {{ $selectedEmployee->department?->name ?? 'No department' }}</p>
                @else
                    <p class="text-xs font-bold tracking-[0.18em] text-blue-600">WORKFORCE INSIGHTS</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Accomplishment reports</h1>
                    <p class="mt-1 text-sm text-slate-500">Review employee output, attachments, and accomplishment reports that need a decision.</p>
                @endif
            </div>
            <a href="{{ route('reports.accomplishments.export', $exportParameters) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Download CSV</a>
        </div>

        <form action="{{ $currentRoute }}" method="GET" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <label class="block"><span class="text-xs font-bold uppercase tracking-wide text-slate-500">From</span><input type="date" name="from" value="{{ $filters['from'] }}" class="mt-2 w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"></label>
                <label class="block"><span class="text-xs font-bold uppercase tracking-wide text-slate-500">To</span><input type="date" name="to" value="{{ $filters['to'] }}" class="mt-2 w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"></label>
                <label class="block"><span class="text-xs font-bold uppercase tracking-wide text-slate-500">Department</span><select name="department_id" class="mt-2 w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"><option value="">All departments</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected((string) $filters['department_id'] === (string) $department->id)>{{ $department->name }}</option>@endforeach</select></label>
                <label class="block"><span class="text-xs font-bold uppercase tracking-wide text-slate-500">Review status</span><select name="status" class="mt-2 w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"><option value="">All statuses</option><option value="submitted" @selected($filters['status'] === 'submitted')>Pending review</option><option value="reviewed" @selected($filters['status'] === 'reviewed')>Reviewed</option><option value="revision" @selected($filters['status'] === 'revision')>Revision requested</option></select></label>
                <div class="flex items-end gap-3"><button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Apply</button><a href="{{ $currentRoute }}" class="pb-2.5 text-sm font-semibold text-slate-500 hover:text-slate-800">Clear</a></div>
            </div>
        </form>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ([
                ['Submitted reports', $summary['reports'], 'within selected period', 'border-slate-200 bg-white text-slate-900', 'text-slate-500'],
                ['Reviewed', $summary['reviewed'], 'reports reviewed', 'border-emerald-100 bg-emerald-50 text-emerald-900', 'text-emerald-700'],
                ['Pending review', $summary['pending'], 'waiting for review', 'border-amber-100 bg-amber-50 text-amber-900', 'text-amber-700'],
                ['Revision requested', $summary['revisions'], 'needs employee update', 'border-rose-100 bg-rose-50 text-rose-900', 'text-rose-700'],
                ['Output files', $summary['attachments'], 'files attached', 'border-indigo-100 bg-indigo-50 text-indigo-900', 'text-indigo-700'],
            ] as [$label, $value, $description, $cardClass, $labelClass])
                <article class="rounded-2xl border p-5 shadow-sm {{ $cardClass }}"><p class="text-sm font-medium {{ $labelClass }}">{{ $label }}</p><p class="mt-2 text-3xl font-bold tracking-tight">{{ $value }}</p><p class="mt-2 text-xs {{ $labelClass }}">{{ $description }}</p></article>
            @endforeach
        </div>

        @if (! $isEmployeeDetail)
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-bold text-slate-900">Employees</h2><p class="mt-1 text-sm text-slate-500">Open an employee to review their accomplishment entries and output files.</p></div>
                <div class="divide-y divide-slate-100">
                    @forelse ($employees as $employee)
                        <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-semibold text-slate-900">{{ trim($employee->first_name.' '.$employee->last_name) }}</p><p class="mt-1 text-sm text-slate-500">{{ $employee->employee_number }} · {{ $employee->department?->name ?? 'No department' }}</p></div><a href="{{ route('reports.accomplishments.employee', array_merge(['employee' => $employee], array_filter($filters))) }}" class="inline-flex w-fit items-center rounded-lg border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-600 hover:border-blue-300 hover:bg-blue-50">View accomplishments →</a></div>
                    @empty
                        <div class="px-6 py-14 text-center text-sm text-slate-500">No employees have submitted an accomplishment report in this period.</div>
                    @endforelse
                </div>
            </section>
        @else
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-bold text-slate-900">Accomplishment history</h2><p class="mt-1 text-sm text-slate-500">Entries, review notes, and uploaded output for the selected period.</p></div>
                <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-4">Workday</th><th class="px-6 py-4">Accomplishment</th><th class="px-6 py-4">Output files</th><th class="px-6 py-4">Status</th><th class="px-6 py-4"></th></tr></thead><tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($reports as $report)
                        @php
                            $isRevisionRequested = $report->revision_requested_at !== null;
                            $statusLabel = $isRevisionRequested ? 'Revision requested' : \Illuminate\Support\Str::headline($report->status);
                            $statusClass = $isRevisionRequested ? 'bg-rose-50 text-rose-700' : ($report->status === 'reviewed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700');
                        @endphp
                        <tr><td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">{{ $report->date->format('M j, Y') }}</td><td class="px-6 py-4"><p class="font-semibold text-slate-900">{{ $report->title ?? $report->dailyTask?->title ?? 'Accomplishment' }}</p><p class="mt-1 text-xs text-slate-500">{{ $report->category ? ucfirst($report->category).' · ' : '' }}{{ str($report->progress_status ?? 'completed')->headline() }}</p></td><td class="px-6 py-4">{{ $report->output_attachments_count }} file{{ $report->output_attachments_count === 1 ? '' : 's' }}</td><td class="px-6 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span></td><td class="px-6 py-4 text-right"><details class="group inline-block text-left"><summary class="cursor-pointer list-none text-sm font-semibold text-blue-600 hover:text-blue-700">View details</summary><div class="mt-3 w-[min(32rem,80vw)] rounded-xl border border-slate-200 bg-slate-50 p-4 text-left shadow-lg"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Summary</p><p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $report->summary }}</p>@if ($report->blockers)<p class="mt-4 text-xs font-bold uppercase tracking-wide text-slate-500">Blockers</p><p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $report->blockers }}</p>@endif @if ($report->next_steps)<p class="mt-4 text-xs font-bold uppercase tracking-wide text-slate-500">Next steps</p><p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $report->next_steps }}</p>@endif @if ($report->review_note)<p class="mt-4 text-xs font-bold uppercase tracking-wide text-slate-500">Reviewer note</p><p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $report->review_note }}</p>@endif @if ($report->outputAttachments->isNotEmpty())<p class="mt-4 text-xs font-bold uppercase tracking-wide text-slate-500">Uploaded output</p><ul class="mt-2 space-y-2">@foreach ($report->outputAttachments as $attachment)<li class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"><span class="min-w-0 truncate text-slate-700">{{ $attachment->file_name }}</span><a href="{{ route('accomplishments.attachments.download', $attachment) }}" class="shrink-0 font-semibold text-blue-600 hover:text-blue-700">Open</a></li>@endforeach</ul>@endif</div></details></td></tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-14 text-center text-sm text-slate-500">No accomplishment reports match these filters.</td></tr>
                    @endforelse
                </tbody></table></div>
                @if ($reports->hasPages())<div class="border-t border-slate-200 px-6 py-4">{{ $reports->links() }}</div>@endif
            </section>
        @endif
    </div>
</x-dashboard-layout>
