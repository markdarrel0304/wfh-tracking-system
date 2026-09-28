<x-dashboard-layout title="Accomplishments">
    @php
        $reportStatus = static fn ($report): array => $report->submitted_at === null
            ? ['classes' => 'bg-slate-100 text-slate-700', 'label' => 'Draft']
            : ($report->revision_requested_at
                ? ['classes' => 'bg-rose-50 text-rose-700', 'label' => 'Changes requested']
                : ($report->status === 'reviewed'
                    ? ['classes' => 'bg-emerald-50 text-emerald-700', 'label' => 'Reviewed']
                    : ['classes' => 'bg-amber-50 text-amber-700', 'label' => 'Submitted']));
        $categories = [
            'development' => 'Development',
            'documentation' => 'Documentation',
            'design' => 'Design',
            'testing' => 'Testing',
            'meeting' => 'Meeting',
            'other' => 'Other',
        ];
        $progressStatuses = [
            'completed' => 'Completed',
            'in-progress' => 'In progress',
            'blocked' => 'Blocked',
        ];
        $entryTitle = old('title', $editingReport?->title ?? $editingReport?->dailyTask?->title);
        $entryCategory = old('category', $editingReport?->category ?? 'development');
        $entryProgressStatus = old('progress_status', $editingReport?->progress_status ?? 'completed');
        $entrySummary = old('summary', $editingReport?->summary);
    @endphp

    <div class="mx-auto max-w-4xl space-y-6 pb-8">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Accomplishments</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Your accomplishments</h1>
                <p class="mt-1 text-sm text-slate-600">Record completed work directly—no task setup required.</p>
            </div>
            <form method="GET" action="{{ route('accomplishments.reports') }}" class="flex items-end gap-2">
                <div class="w-48">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Workday</label>
                    <div class="mt-2">
                        <x-wfh-date-picker name="date" selectedDate="{{ $selectedDate->toDateString() }}" title="Select workday" :showQuickSelect="false" :required="true" route="{{ route('accomplishments.reports') }}" year="{{ $calendarMonth->year }}" month="{{ $calendarMonth->month }}" :years="$calendarYears" :months="$calendarMonths" :calendarDays="$calendarDays" />
                    </div>
                </div>
                <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">View day</button>
            </form>
        </section>

        @if (session('report_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('report_success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-semibold">Please correct the highlighted information.</p>
                <ul class="mt-1 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($editingReport?->revision_requested_at)
            <section class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
                <p class="font-semibold">Changes requested</p>
                <p class="mt-1">Update this accomplishment and submit it again for review.</p>
                <p class="mt-3 rounded-lg bg-white/70 px-3 py-2"><span class="font-semibold">Reviewer note:</span> {{ $editingReport->review_note }}</p>
            </section>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">{{ $editingReport ? 'Edit entry' : 'New entry' }}</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-950">{{ $editingReport ? 'Update your accomplishment' : 'What did you accomplish?' }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $selectedDate->format('l, F j, Y') }}</p>
                </div>
                @if ($editingReport)
                    <a href="{{ route('accomplishments.reports', ['date' => $selectedDate->toDateString()]) }}" class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">Start a new entry</a>
                @endif
            </div>

            <form method="POST" action="{{ route('accomplishments.reports.store') }}" enctype="multipart/form-data" class="space-y-5 p-5 sm:p-6" x-data="{ characterCount: {{ mb_strlen($entrySummary ?? '') }}, fileLabel: 'Drag & drop files here or browse' }">
                @csrf
                <input type="hidden" name="date" value="{{ $selectedDate->toDateString() }}">
                @if ($editingReport)<input type="hidden" name="accomplishment_report_id" value="{{ $editingReport->id }}">@endif

                <div class="grid gap-5 md:grid-cols-[minmax(0,1.45fr)_minmax(12rem,0.75fr)]">
                    <div>
                        <label for="title" class="block text-sm font-semibold text-slate-800">Accomplishment title <span class="text-rose-600">*</span></label>
                        <input id="title" name="title" value="{{ $entryTitle }}" maxlength="150" required placeholder="e.g. Finished attendance report module" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-semibold text-slate-800">Category</label>
                        <select id="category" name="category" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach ($categories as $value => $label)
                                <option value="{{ $value }}" @selected($entryCategory === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <fieldset>
                    <legend class="block text-sm font-semibold text-slate-800">Status</legend>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($progressStatuses as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="progress_status" value="{{ $value }}" class="peer sr-only" @checked($entryProgressStatus === $value)>
                                <span class="block rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:text-white hover:border-blue-300">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div>
                    <div class="flex items-end justify-between gap-4">
                        <label for="summary" class="block text-sm font-semibold text-slate-800">What did you accomplish? <span class="text-rose-600">*</span></label>
                        <span class="text-xs font-medium text-slate-500" x-text="characterCount + ' / 500'"></span>
                    </div>
                    <textarea id="summary" name="summary" rows="5" maxlength="500" required x-on:input="characterCount = $event.target.value.length" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Briefly describe the work, results, and any issues encountered.">{{ $entrySummary }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">Be specific—your supervisor will review this.</p>
                </div>

                <div>
                    <label for="attachments" class="block text-sm font-semibold text-slate-800">Attachments <span class="font-normal text-slate-400">(optional)</span></label>
                    <label for="attachments" class="mt-2 flex min-h-28 cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-5 text-center transition hover:border-blue-300 hover:bg-blue-50">
                        <svg class="h-7 w-7 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12.5 4.8-4.8a3.25 3.25 0 1 1 4.6 4.6l-6.2 6.2a5 5 0 0 1-7.1-7.1l6.3-6.3" /></svg>
                        <span class="mt-3 text-sm font-semibold text-slate-800" x-text="fileLabel"></span>
                        <span class="mt-1 text-xs text-slate-500">PDF, PNG, JPG, DOCX, XLSX, CSV, PPT, or ZIP · up to 5 files · 10 MB each</span>
                    </label>
                    <input id="attachments" type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.jpg,.jpeg,.png,.zip" class="sr-only" x-on:change="fileLabel = $event.target.files.length ? $event.target.files.length + ' file' + ($event.target.files.length === 1 ? '' : 's') + ' selected' : 'Drag & drop files here or browse'">
                </div>

                @if ($editingReport?->outputAttachments->isNotEmpty())
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-semibold text-slate-800">Already attached</p>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($editingReport->outputAttachments as $attachment)
                                <a href="{{ route('accomplishments.attachments.download', $attachment) }}" class="flex min-w-0 items-center gap-3 rounded-lg border border-slate-200 bg-white p-3 text-sm font-semibold text-slate-800 transition hover:border-blue-200 hover:bg-blue-50">
                                    <span class="rounded bg-blue-100 px-2 py-1 text-xs font-bold uppercase text-blue-700">{{ pathinfo($attachment->file_name, PATHINFO_EXTENSION) ?: 'file' }}</span>
                                    <span class="truncate">{{ $attachment->file_name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex flex-col-reverse justify-end gap-3 border-t border-slate-100 pt-6 sm:flex-row">
                    <button type="submit" name="submission_action" value="draft" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">Save draft</button>
                    <button type="submit" name="submission_action" value="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">{{ $editingReport?->revision_requested_at ? 'Update and submit' : 'Submit accomplishment' }}</button>
                </div>
            </form>
        </section>

        <section id="history" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">History</p>
                <h2 class="mt-1 text-lg font-semibold text-slate-950">Saved accomplishments for {{ $selectedDate->format('M j, Y') }}</h2>
                <p class="mt-1 text-sm text-slate-600">Drafts, submitted work, and review results stay together.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($dayReports as $report)
                    @php($status = $reportStatus($report))
                    <article class="flex flex-col gap-3 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-slate-900">{{ $report->title ?? $report->dailyTask?->title ?? 'Accomplishment' }}</p>
                                @if ($report->category)<span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $categories[$report->category] ?? ucfirst($report->category) }}</span>@endif
                                @if ($report->progress_status)<span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $progressStatuses[$report->progress_status] ?? ucfirst($report->progress_status) }}</span>@endif
                            </div>
                            <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $report->summary }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="text-xs text-slate-400">{{ $report->outputAttachments->count() }} file{{ $report->outputAttachments->count() === 1 ? '' : 's' }}</span>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['classes'] }}">{{ $status['label'] }}</span>
                            <a href="{{ route('accomplishments.reports', ['date' => $selectedDate->toDateString(), 'edit' => $report->id]) }}" class="text-sm font-semibold text-blue-700 transition hover:text-blue-900">Open</a>
                        </div>
                    </article>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-slate-500">No accomplishments saved for this day yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-dashboard-layout>
