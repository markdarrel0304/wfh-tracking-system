<x-dashboard-layout title="Schedule Details">
    @php
        $weekdays = [
            'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 'thursday' => 'Thu',
            'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun',
        ];
        $timeValue = static fn (?string $time): string => $time ? substr($time, 0, 5) : '';
        $displayTime = static fn (?string $time): string => $time ? \Carbon\Carbon::parse($time)->format('g:i A') : '—';
        $defaultAssignments = $employees->where('work_schedule_id', $workSchedule->id);
    @endphp

    <div class="mx-auto max-w-6xl space-y-6 pb-6">
        <a href="{{ route('admin.work-schedules') }}" class="inline-flex text-sm font-semibold text-blue-600 hover:text-blue-700">← Back to schedule templates</a>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Schedule template</p>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{{ $workSchedule->name }}</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ $workSchedule->employees_count }} employee(s) use this as their default schedule.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Template details</span>
            </div>
            <dl class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2 lg:grid-cols-3">
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Working days</dt><dd class="mt-1 font-semibold text-slate-900">{{ collect($workSchedule->days_json ?? [])->map(fn (string $day): string => $weekdays[$day] ?? ucfirst($day))->join(' · ') }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Regular hours</dt><dd class="mt-1 font-semibold text-slate-900">{{ $displayTime($workSchedule->time_in) }}–{{ $displayTime($workSchedule->time_out) }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Overtime</dt><dd class="mt-1 font-semibold text-slate-900">{{ $displayTime($workSchedule->overtime_start) }} · {{ $workSchedule->overtime_minimum_minutes }}–{{ $workSchedule->overtime_maximum_minutes }} min</dd></div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Default assignments</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Employees assigned to this schedule by default</h2>
                <p class="mt-1 text-sm text-slate-600">Removing a default schedule leaves the employee with no schedule unless another administrator assigns one.</p>
            </div>
            @forelse ($defaultAssignments as $employee)
                <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-4 last:border-b-0 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-slate-900">{{ $employee->first_name }} {{ $employee->last_name }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $employee->employee_number }}{{ $employee->department ? ' · '.$employee->department->name : '' }}</p>
                    </div>
                    <button
                        type="button"
                        x-data=""
                        x-on:click="$dispatch('open-modal', 'remove-default-schedule-{{ $employee->id }}')"
                        class="inline-flex w-full justify-center rounded-lg border border-rose-300 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-400/50 dark:bg-rose-950/40 dark:text-rose-200 dark:hover:bg-rose-950/70 sm:w-auto"
                    >
                        Remove default schedule
                    </button>

                    <x-modal name="remove-default-schedule-{{ $employee->id }}" maxWidth="md" focusable>
                        <form method="POST" action="{{ route('admin.work-schedules.assign', $employee) }}" class="p-6">
                            @csrf
                            @method('PATCH')

                            <div class="flex items-start gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300" aria-hidden="true">!</span>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-600 dark:text-rose-300">Remove assignment</p>
                                    <h3 class="mt-1 text-lg font-semibold text-slate-950 dark:text-white">Remove {{ $employee->first_name }}’s default schedule?</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $employee->first_name }} will have no default work schedule until an administrator assigns another one. Their previous attendance records will not be changed.</p>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                <button type="button" x-on:click="$dispatch('close-modal', 'remove-default-schedule-{{ $employee->id }}')" class="inline-flex justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">Keep schedule</button>
                                <button type="submit" class="inline-flex justify-center rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800">Remove schedule</button>
                            </div>
                        </form>
                    </x-modal>
                </div>
            @empty
                <p class="px-6 py-10 text-center text-sm text-slate-500">No employees have this as their default schedule.</p>
            @endforelse
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Step 2 of 2</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Assign employees</h2>
                <p class="mt-1 text-sm text-slate-600">Select one or more employees, choose when this schedule starts, then save the assignment.</p>
            </div>
            @if ($errors->any())
                <div class="mx-6 mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('admin.work-schedules.assignments.store', $workSchedule) }}">
                @csrf
                <div class="grid gap-4 border-b border-slate-200 bg-slate-50 px-6 py-5 sm:grid-cols-[minmax(0,1fr)_220px] sm:items-end">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Choose employees</p>
                        <p class="mt-1 text-sm text-slate-500">They will receive an in-app notification about the schedule and its start date.</p>
                    </div>
                    <div>
                        <label for="effective-date" class="block text-sm font-semibold text-slate-800">Effective date</label>
                        <input id="effective-date" name="effective_date" type="date" value="{{ old('effective_date', today()->toDateString()) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700/80">
                    @forelse ($employees as $employee)
                        <label class="flex cursor-pointer items-center gap-4 px-6 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/70">
                            <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" @checked(in_array($employee->id, old('employee_ids', []))) class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold text-slate-900">{{ $employee->first_name }} {{ $employee->last_name }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500">{{ $employee->employee_number }}{{ $employee->department ? ' · '.$employee->department->name : '' }}</span>
                            </span>
                            <span class="hidden text-right text-xs text-slate-500 sm:block">Default: {{ $employee->workSchedule?->name ?? 'No schedule' }}</span>
                        </label>
                    @empty
                        <p class="px-6 py-12 text-center text-sm text-slate-500">No employees are available to assign.</p>
                    @endforelse
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 px-6 py-5 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.work-schedules') }}" class="inline-flex justify-center rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Done</a>
                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Assign selected employees</button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Schedule overrides</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Employees temporarily using this schedule</h2>
                <p class="mt-1 text-sm text-slate-600">Revert an override to return the employee to their default schedule. Future overrides are cancelled from their start date; active overrides end today.</p>
            </div>
            @forelse ($scheduledOverrides as $assignment)
                <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-4 last:border-b-0 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-slate-900">{{ $assignment->employee->first_name }} {{ $assignment->employee->last_name }}</p>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $assignment->employee->employee_number }}{{ $assignment->employee->department ? ' · '.$assignment->employee->department->name : '' }}
                            <span class="mx-1">·</span> {{ $assignment->effective_date->isFuture() ? 'Starts' : 'Started' }} {{ $assignment->effective_date->format('M j, Y') }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">Default schedule: {{ $assignment->employee->workSchedule?->name ?? 'No schedule assigned' }}</p>
                    </div>
                    <button
                        type="button"
                        x-data=""
                        x-on:click="$dispatch('open-modal', 'revert-schedule-override-{{ $assignment->id }}')"
                        class="inline-flex w-full justify-center rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 transition hover:bg-amber-100 dark:border-amber-400/50 dark:bg-amber-950/40 dark:text-amber-200 dark:hover:bg-amber-950/70 sm:w-auto"
                    >
                        {{ $assignment->effective_date->isFuture() ? 'Cancel override' : 'Revert to default' }}
                    </button>

                    <x-modal name="revert-schedule-override-{{ $assignment->id }}" maxWidth="md" focusable>
                        <form method="POST" action="{{ route('admin.work-schedules.assignments.revert', [$workSchedule, $assignment]) }}" class="p-6">
                            @csrf
                            @method('PATCH')

                            <div class="flex items-start gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300" aria-hidden="true">↺</span>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700 dark:text-amber-300">Schedule override</p>
                                    <h3 class="mt-1 text-lg font-semibold text-slate-950 dark:text-white">{{ $assignment->effective_date->isFuture() ? 'Cancel this scheduled override?' : 'Return to the default schedule?' }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $assignment->employee->first_name }} will {{ $assignment->effective_date->isFuture() ? 'keep their current default schedule.' : 'use their default schedule again from today.' }}</p>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                <button type="button" x-on:click="$dispatch('close-modal', 'revert-schedule-override-{{ $assignment->id }}')" class="inline-flex justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">Keep override</button>
                                <button type="submit" class="inline-flex justify-center rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800">{{ $assignment->effective_date->isFuture() ? 'Cancel override' : 'Revert schedule' }}</button>
                            </div>
                        </form>
                    </x-modal>
                </div>
            @empty
                <p class="px-6 py-10 text-center text-sm text-slate-500">No employees are currently using this schedule as an override.</p>
            @endforelse
        </section>

        <details class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <summary class="cursor-pointer px-6 py-5 font-semibold text-slate-900">Edit schedule settings</summary>
            <form method="POST" action="{{ route('admin.work-schedules.update', $workSchedule) }}" class="space-y-6 border-t border-slate-200 p-6">
                @csrf
                @method('PATCH')
                @include('admin.partials.work-schedule-fields', ['schedule' => $workSchedule, 'weekdays' => $weekdays, 'timeValue' => $timeValue])
                <div class="flex justify-end border-t border-slate-100 pt-5">
                    <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">Update schedule settings</button>
                </div>
            </form>
        </details>
    </div>
</x-dashboard-layout>
