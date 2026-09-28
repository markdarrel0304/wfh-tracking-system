<x-dashboard-layout title="Review Attendance Correction">
    <div class="mx-auto max-w-6xl space-y-6 pb-6">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Admin review</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Review attendance correction</h1>
                <p class="mt-2 text-sm text-slate-600">Review the requested change and supporting proof before making a decision.</p>
            </div>
            <a href="{{ route('approvals.attendance-corrections') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Back to corrections</a>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $attendanceCorrection->employee->first_name }} {{ $attendanceCorrection->employee->last_name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $attendanceCorrection->employee->user->email }}@if ($attendanceCorrection->employee->department)<span class="px-1 text-slate-300">•</span>{{ $attendanceCorrection->employee->department->name }}@endif</p>
                </div>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Pending review</span>
            </div>

            <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
                <div class="space-y-5">
                    <dl class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Workday</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $attendanceCorrection->attendance->date->format('M j, Y') }}</dd>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recorded times</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $attendanceCorrection->attendance->formattedTimeIn() ?? '—' }} – {{ $attendanceCorrection->attendance->formattedTimeOut() ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl bg-blue-50 px-4 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-blue-700">Requested times</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $attendanceCorrection->requested_time_in ? date('g:i A', strtotime($attendanceCorrection->requested_time_in)) : 'No change' }} – {{ $attendanceCorrection->requested_time_out ? date('g:i A', strtotime($attendanceCorrection->requested_time_out)) : 'No change' }}</dd>
                        </div>
                    </dl>

                    <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Employee reason</p>
                        <p class="mt-1 text-sm leading-6 text-slate-700">{{ $attendanceCorrection->reason }}</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Supporting proof</p>
                        @if ($attendanceCorrection->supporting_document)
                            <a href="{{ route('attendance.corrections.supporting-document', $attendanceCorrection) }}" class="mt-3 inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">Download uploaded proof</a>
                        @else
                            <p class="mt-2 text-sm text-slate-500">No supporting proof was uploaded.</p>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('approvals.attendance-corrections.update', $attendanceCorrection) }}" class="h-fit rounded-xl border border-slate-200 bg-slate-50 p-4">
                    @csrf
                    @method('PATCH')
                    <label for="remarks" class="text-sm font-semibold text-slate-800">Decision note <span class="font-normal text-slate-400">(required when not approving)</span></label>
                    <textarea id="remarks" name="remarks" rows="6" maxlength="1000" class="mt-2 w-full rounded-lg border-slate-300 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Explain the decision to the employee.">{{ old('remarks') }}</textarea>
                    @error('remarks')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <button type="submit" name="status" value="rejected" class="rounded-lg border border-rose-200 bg-white px-3 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Not approve</button>
                        <button type="submit" name="status" value="approved" class="rounded-lg bg-blue-600 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Approve</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</x-dashboard-layout>
