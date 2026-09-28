<x-dashboard-layout title="Output Attachments">
    <div class="mx-auto max-w-6xl space-y-6 pb-6">
        <section class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white px-6 py-7 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Accomplishments</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Output attachments</h1>
                <p class="mt-2 text-sm text-slate-600">Keep proof of work with the daily report, or connect each file to the task it supports.</p>
            </div>
            <a href="{{ route('accomplishments.reports') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">View reports</a>
        </section>

        @if (session('attachment_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('attachment_success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-semibold">Your files could not be uploaded.</p>
                <ul class="mt-1 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Uploaded files</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $attachmentCount }}</p>
                <p class="mt-1 text-xs text-slate-500">across your recent workdays</p>
            </div>
            <div class="rounded-xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-blue-700">Reports with outputs</p>
                <p class="mt-2 text-3xl font-bold text-blue-900">{{ $reportCountWithAttachments }}</p>
                <p class="mt-1 text-xs text-blue-700">reports supported with evidence</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Upload allowance</p>
                <p class="mt-2 text-xl font-bold text-slate-900">Up to 5 files</p>
                <p class="mt-1 text-xs text-slate-500">Maximum 10 MB per file</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-bold text-slate-900">Add deliverables</h2>
                <p class="mt-1 text-sm text-slate-500">Files are private and available only to you and authorized reviewers. A task link is optional.</p>
            </div>

            @if ($reports->isNotEmpty())
                <form method="POST" action="{{ route('accomplishments.attachments.store') }}" enctype="multipart/form-data" class="grid gap-5 p-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.4fr)_auto] lg:items-start">
                    @csrf
                    <div>
                        <label for="accomplishment_report_id" class="text-sm font-semibold text-slate-800">Daily report</label>
                        <select id="accomplishment_report_id" name="accomplishment_report_id" required class="mt-2 block w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500">
                            @foreach ($reports as $report)
                                <option value="{{ $report->id }}" data-date="{{ $report->date->toDateString() }}" @selected(old('accomplishment_report_id') == $report->id) @disabled($report->status === 'reviewed')>
                                    {{ $report->date->format('M j, Y') }} — {{ ucfirst($report->status) }}{{ $report->status === 'reviewed' ? ' (locked)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="daily_task_id" class="text-sm font-semibold text-slate-800">Related task <span class="font-normal text-slate-400">(optional)</span></label>
                        <select id="daily_task_id" name="daily_task_id" class="mt-2 block w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Daily report only</option>
                            @foreach ($tasksByDate as $date => $tasks)
                                @if ($tasks->isNotEmpty())
                                    <optgroup label="{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}">
                                        @foreach ($tasks as $task)
                                            <option value="{{ $task->id }}" data-date="{{ $date }}" @selected((int) old('daily_task_id') === $task->id)>{{ $task->title }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs leading-5 text-slate-500">Select a task to make the proof easy to verify. Otherwise it supports the full report.</p>
                    </div>
                    <div>
                        <label for="attachments" class="text-sm font-semibold text-slate-800">Files</label>
                        <input id="attachments" type="file" name="attachments[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.jpg,.jpeg,.png,.zip" class="mt-2 block w-full rounded-lg border border-slate-300 bg-slate-50 text-sm text-slate-600 file:mr-4 file:border-0 file:bg-blue-600 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-700">
                        <p class="mt-2 text-xs leading-5 text-slate-500">PDF, Word, Excel, PowerPoint, CSV, JPG, PNG, or ZIP. Maximum 5 files, 10 MB each.</p>
                    </div>
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 lg:mt-8">Upload files</button>
                </form>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="font-semibold text-slate-800">Create a daily report first</p>
                    <p class="mt-1 text-sm text-slate-500">Deliverables are filed with a report so your work is easy to review in context.</p>
                    <a href="{{ route('accomplishments.reports') }}" class="mt-4 inline-flex rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Create daily report</a>
                </div>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-bold text-slate-900">Your deliverables</h2>
                <p class="mt-1 text-sm text-slate-500">Files are grouped with the report they support and labeled with their related task when selected.</p>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($reports as $report)
                    <article class="px-6 py-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="font-semibold text-slate-900">{{ $report->date->format('l, M j, Y') }}</h3>
                                <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">{{ $report->summary }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $report->status === 'reviewed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($report->status) }}</span>
                        </div>

                        @if ($report->outputAttachments->isNotEmpty())
                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                @foreach ($report->outputAttachments as $attachment)
                                    <div class="flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-xs font-bold uppercase text-blue-700">{{ pathinfo($attachment->file_name, PATHINFO_EXTENSION) ?: 'file' }}</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-slate-800" title="{{ $attachment->file_name }}">{{ $attachment->file_name }}</p>
                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $attachment->size_bytes && $attachment->size_bytes >= 1048576 ? number_format($attachment->size_bytes / 1048576, 1).' MB' : number_format(($attachment->size_bytes ?? 0) / 1024, 1).' KB' }}
                                                · {{ $attachment->uploaded_at?->format('M j, g:i A') }}
                                            </p>
                                            <p class="mt-1 truncate text-xs font-medium text-blue-700">{{ $attachment->dailyTask ? 'Task: '.$attachment->dailyTask->title : 'Supports the daily report' }}</p>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-3 text-xs font-semibold">
                                            <a href="{{ route('accomplishments.attachments.download', $attachment) }}" class="text-blue-700 hover:text-blue-900">Download</a>
                                            @if ($report->status !== 'reviewed')
                                                <form method="POST" action="{{ route('accomplishments.attachments.destroy', $attachment) }}" onsubmit="return confirm('Remove this attachment?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-800">Remove</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-4 text-sm text-slate-500">No output files attached to this report yet.</p>
                        @endif
                    </article>
                @empty
                    <div class="px-6 py-14 text-center">
                        <p class="font-semibold text-slate-800">No reports available</p>
                        <p class="mt-1 text-sm text-slate-500">Submit a daily accomplishment report before attaching output files.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const reportSelect = document.getElementById('accomplishment_report_id');
            const taskSelect = document.getElementById('daily_task_id');

            if (!reportSelect || !taskSelect) {
                return;
            }

            const syncTaskOptions = () => {
                const reportDate = reportSelect.selectedOptions[0]?.dataset.date;

                Array.from(taskSelect.options).forEach((option) => {
                    option.hidden = Boolean(option.dataset.date && option.dataset.date !== reportDate);
                    option.disabled = Boolean(option.dataset.date && option.dataset.date !== reportDate);
                });

                if (taskSelect.selectedOptions[0]?.disabled) {
                    taskSelect.value = '';
                }
            };

            reportSelect.addEventListener('change', syncTaskOptions);
            syncTaskOptions();
        });
    </script>
</x-dashboard-layout>
