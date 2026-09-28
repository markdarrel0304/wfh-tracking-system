<x-dashboard-layout title="Dashboard">
    @php
        $isAdmin = auth()->user()->role === 'admin';
        $todayStatus = $todayStatus ?? 'Not Clocked In';
        $statusStyle = match ($todayStatus) {
            'Clocked In' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            'Clocked Out' => 'bg-slate-100 text-slate-700 ring-slate-200',
            default => 'bg-amber-50 text-amber-700 ring-amber-100',
        };
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 pb-6">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-6 px-6 py-7 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">{{ $isAdmin ? 'Operations overview' : 'Employee workspace' }}</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Good day, {{ auth()->user()->name }}.</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ $isAdmin ? 'Stay ahead of workforce activity, outstanding requests, and attendance across the organization.' : 'Keep your schedule, attendance, and remote-work requests organized in one place.' }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Today</p>
                        <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ now()->format('l, M j') }}</p>
                    </div>
                    @if ($isAdmin)
                        <a href="{{ route('wfh.approval') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Review requests
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('wfh.requests.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            New WFH request
                        </a>
                    @endif
                </div>
            </div>
            <div class="h-1 bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-400"></div>
        </section>

        @if ($isAdmin)
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total employees</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $employeeCount }}</p>
                            <p class="mt-2 text-xs text-slate-500">People in the organization</p>
                        </div>
                        <span class="rounded-lg bg-blue-50 p-3 text-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477" />
                            </svg>
                        </span>
                    </div>
                </article>

                <article class="rounded-xl border border-amber-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Pending WFH requests</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $pendingWfhRequests }}</p>
                            <p class="mt-2 text-xs text-amber-700">Awaiting your review</p>
                        </div>
                        <span class="rounded-lg bg-amber-50 p-3 text-amber-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </span>
                    </div>
                </article>

                <article class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Attendance corrections</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $pendingAttendanceCorrections }}</p>
                            <p class="mt-2 text-xs text-emerald-700">Awaiting review</p>
                        </div>
                        <span class="rounded-lg bg-emerald-50 p-3 text-emerald-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                </article>

                <article class="rounded-xl border border-indigo-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Accomplishments</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $pendingAccomplishmentReports }}</p>
                            <p class="mt-2 text-xs text-indigo-700">Waiting for review</p>
                        </div>
                        <span class="rounded-lg bg-indigo-50 p-3 text-indigo-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-3">
                <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Priority queue</p>
                            <h3 class="mt-1 text-lg font-bold text-slate-900">Work From Home approvals</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Keep remote work planning moving by reviewing outstanding employee requests.</p>
                        </div>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-sm font-bold text-amber-700">{{ $pendingWfhRequests }} pending</span>
                    </div>
                    <div class="mt-6 flex flex-col gap-4 rounded-lg bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-blue-600 shadow-sm">01</span>
                            <p class="text-sm font-medium text-slate-700">Review the request details and make an informed decision.</p>
                        </div>
                        <a href="{{ route('wfh.approval') }}" class="shrink-0 text-sm font-semibold text-blue-600 hover:text-blue-700">Open queue →</a>
                    </div>
                </article>

                <article class="rounded-xl bg-slate-900 p-6 text-white shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-300">Management tools</p>
                    <h3 class="mt-2 text-lg font-bold">Keep the team aligned.</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Use the core workspace tools to manage employees, schedules, and reports.</p>
                    <div class="mt-6 space-y-3">
                        <a href="{{ route('employees.index') }}" class="flex items-center justify-between rounded-lg bg-white/10 px-4 py-3 text-sm font-semibold transition hover:bg-white/15"><span>Employees</span><span>→</span></a>
                        <a href="{{ route('reports.wfh') }}" class="flex items-center justify-between rounded-lg bg-white/10 px-4 py-3 text-sm font-semibold transition hover:bg-white/15"><span>WFH reports</span><span>→</span></a>
                    </div>
                </article>
            </section>

            <section class="grid gap-6 xl:grid-cols-5">
                <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm xl:col-span-3">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5"><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Today’s attendance</p><h3 class="mt-1 text-lg font-bold text-slate-900">{{ $todayAttendance }} recorded employee{{ $todayAttendance === 1 ? '' : 's' }}</h3><p class="mt-1 text-sm text-slate-600">Most recent attendance activity for {{ now()->format('M j, Y') }}.</p></div><a href="{{ route('reports.attendance') }}" class="shrink-0 text-sm font-semibold text-blue-600 hover:text-blue-700">Attendance report →</a></div>
                    <div class="divide-y divide-slate-100">@forelse ($todayAttendanceRecords as $attendanceRecord)<div class="flex flex-col gap-2 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-semibold text-slate-900">{{ $attendanceRecord->employee->first_name }} {{ $attendanceRecord->employee->last_name }}</p><p class="mt-1 text-xs text-slate-500">{{ $attendanceRecord->employee->department?->name ?? 'No department' }}</p></div><div class="flex items-center gap-3"><span class="text-sm text-slate-600">{{ $attendanceRecord->formattedTimeIn() ?? '—' }} — {{ $attendanceRecord->formattedTimeOut() ?? 'In progress' }}</span><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $attendanceRecord->time_out ? 'bg-slate-100 text-slate-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $attendanceRecord->time_out ? 'Completed' : 'Clocked in' }}</span></div></div>@empty<p class="px-6 py-10 text-center text-sm text-slate-500">No attendance has been recorded today.</p>@endforelse</div>
                </article>
                <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2"><p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Review queue</p><h3 class="mt-1 text-lg font-bold text-slate-900">What needs attention</h3><p class="mt-1 text-sm leading-6 text-slate-600">Open the queue that needs your decision next.</p><div class="mt-5 space-y-3"><a href="{{ route('wfh.approval') }}" class="flex items-center justify-between rounded-lg border border-amber-100 bg-amber-50 px-4 py-3 transition hover:border-amber-200"><span><span class="block text-sm font-semibold text-amber-900">WFH requests</span><span class="mt-0.5 block text-xs text-amber-700">{{ $pendingWfhRequests }} awaiting review</span></span><span class="text-amber-700">→</span></a><a href="{{ route('approvals.attendance-corrections') }}" class="flex items-center justify-between rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 transition hover:border-emerald-200"><span><span class="block text-sm font-semibold text-emerald-900">Attendance corrections</span><span class="mt-0.5 block text-xs text-emerald-700">{{ $pendingAttendanceCorrections }} awaiting review</span></span><span class="text-emerald-700">→</span></a><a href="{{ route('approvals.accomplishment-reports') }}" class="flex items-center justify-between rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3 transition hover:border-indigo-200"><span><span class="block text-sm font-semibold text-indigo-900">Accomplishments</span><span class="mt-0.5 block text-xs text-indigo-700">{{ $pendingAccomplishmentReports }} awaiting review</span></span><span class="text-indigo-700">→</span></a></div></article>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"><div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5"><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Pending WFH</p><h3 class="mt-1 text-lg font-bold text-slate-900">Oldest requests first</h3></div><a href="{{ route('wfh.approval') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">View all →</a></div><div class="divide-y divide-slate-100">@forelse ($recentPendingWfhRequests as $request)<div class="flex items-center justify-between gap-4 px-6 py-4"><div class="min-w-0"><p class="truncate font-semibold text-slate-900">{{ $request->employee->first_name }} {{ $request->employee->last_name }}</p><p class="mt-1 truncate text-xs text-slate-500">{{ $request->date_from->format('M j') }}–{{ $request->date_to->format('M j, Y') }} · {{ $request->employee->department?->name ?? 'No department' }}</p></div><a href="{{ route('wfh.approval.review', $request) }}" class="shrink-0 text-sm font-semibold text-blue-600 hover:text-blue-700">Review</a></div>@empty<p class="px-6 py-10 text-center text-sm text-slate-500">No WFH requests are waiting.</p>@endforelse</div></article>
                <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"><div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5"><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Pending accomplishments</p><h3 class="mt-1 text-lg font-bold text-slate-900">Completed work to review</h3></div><a href="{{ route('approvals.accomplishment-reports') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">View all →</a></div><div class="divide-y divide-slate-100">@forelse ($recentPendingReports as $report)<div class="flex items-center justify-between gap-4 px-6 py-4"><div class="min-w-0"><p class="truncate font-semibold text-slate-900">{{ $report->title ?? $report->dailyTask?->title ?? 'Accomplishment' }}</p><p class="mt-1 truncate text-xs text-slate-500">{{ $report->employee->first_name }} {{ $report->employee->last_name }} · {{ $report->date->format('M j, Y') }} · {{ $report->output_attachments_count }} file{{ $report->output_attachments_count === 1 ? '' : 's' }}</p></div><a href="{{ route('approvals.accomplishment-reports') }}" class="shrink-0 text-sm font-semibold text-blue-600 hover:text-blue-700">Review</a></div>@empty<p class="px-6 py-10 text-center text-sm text-slate-500">No accomplishment reports are waiting.</p>@endforelse</div></article>
            </section>
        @else
            <section class="grid gap-4 md:grid-cols-3">
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">WFH requests</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $myWfhRequests }}</p>
                            <p class="mt-2 text-xs text-slate-500">Requests submitted to date</p>
                        </div>
                        <span class="rounded-lg bg-blue-50 p-3 text-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8.688c0-.855.447-1.65 1.162-2.072l6.603-3.89c.576-.34 1.283-.34 1.859 0l6.603 3.89c.715.422 1.162 1.217 1.162 2.072V21H3V8.688z" />
                            </svg>
                        </span>
                    </div>
                </article>

                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Attendance records</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $myAttendance }}</p>
                            <p class="mt-2 text-xs text-slate-500">Your recorded workdays</p>
                        </div>
                        <span class="rounded-lg bg-emerald-50 p-3 text-emerald-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                </article>

                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Today’s attendance</p>
                            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $todayStatus }}</p>
                            <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusStyle }}">Live status</span>
                        </div>
                        <span class="rounded-lg bg-indigo-50 p-3 text-indigo-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-5">
                <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-3">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Today at a glance</p>
                            <h3 class="mt-1 text-lg font-bold text-slate-900">Your workday status</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Keep your attendance current and make sure your work arrangements are up to date.</p>
                        </div>
                        <span class="inline-flex w-fit rounded-full px-3 py-1.5 text-sm font-semibold ring-1 ring-inset {{ $statusStyle }}">{{ $todayStatus }}</span>
                    </div>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <a href="{{ route('attendance.time-in-out') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/50">
                            <div class="flex items-center justify-between"><span class="text-sm font-semibold text-slate-800">Time in / out</span><span class="text-blue-600 transition group-hover:translate-x-0.5">→</span></div>
                            <p class="mt-2 text-sm leading-5 text-slate-500">Record your attendance for today.</p>
                        </a>
                        <a href="{{ route('my-work-schedule') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/50">
                            <div class="flex items-center justify-between"><span class="text-sm font-semibold text-slate-800">My schedule</span><span class="text-blue-600 transition group-hover:translate-x-0.5">→</span></div>
                            <p class="mt-2 text-sm leading-5 text-slate-500">Review your planned shifts and rest days.</p>
                        </a>
                    </div>
                </article>

                <article class="rounded-xl border border-blue-100 bg-blue-50/50 p-6 shadow-sm lg:col-span-2">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Remote work</p>
                    <h3 class="mt-1 text-lg font-bold text-slate-900">Plan ahead for WFH.</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Submit requests early so your supervisor has time to review them.</p>
                    <ul class="mt-5 space-y-3 text-sm text-slate-700">
                        <li class="flex gap-3"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">1</span><span>Choose your dates and working hours.</span></li>
                        <li class="flex gap-3"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">2</span><span>Add a concise reason for your request.</span></li>
                        <li class="flex gap-3"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">3</span><span>Track the decision in My Requests.</span></li>
                    </ul>
                    <a href="{{ route('wfh.requests.create') }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-800">Start a request <span>→</span></a>
                </article>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Workspace shortcuts</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Common tasks</h3>
                    </div>
                    <a href="{{ route('wfh.my-requests') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">View all requests →</a>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <a href="{{ route('my-profile') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-blue-300 hover:shadow-sm">
                        <p class="text-sm font-semibold text-slate-800">My profile</p>
                        <p class="mt-1 text-sm leading-5 text-slate-500">Update your personal details.</p>
                        <p class="mt-3 text-sm font-semibold text-blue-600">Open profile →</p>
                    </a>
                    <a href="{{ route('wfh.my-requests') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-blue-300 hover:shadow-sm">
                        <p class="text-sm font-semibold text-slate-800">My WFH requests</p>
                        <p class="mt-1 text-sm leading-5 text-slate-500">Check request history and decisions.</p>
                        <p class="mt-3 text-sm font-semibold text-blue-600">View requests →</p>
                    </a>
                    <a href="{{ route('accomplishments.reports') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-blue-300 hover:shadow-sm">
                        <p class="text-sm font-semibold text-slate-800">Accomplishments</p>
                        <p class="mt-1 text-sm leading-5 text-slate-500">Record completed work, your summary, and proof in one place.</p>
                        <p class="mt-3 text-sm font-semibold text-blue-600">Open accomplishments →</p>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-blue-300 hover:shadow-sm">
                        <p class="text-sm font-semibold text-slate-800">Notifications</p>
                        <p class="mt-1 text-sm leading-5 text-slate-500">Review recent request activity.</p>
                        <p class="mt-3 text-sm font-semibold text-blue-600">Open notifications →</p>
                    </a>
                </div>
            </section>
        @endif
    </div>
</x-dashboard-layout>
