<x-dashboard-layout title="Departments Management">
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Administration</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Departments</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">Manage the company’s departments, locations, employee assignment, and active status.</p>
            </div>
            <a href="#new-department" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Add department</a>
        </section>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-semibold">Please review the department details.</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section id="new-department" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-950">Add a department</h2>
                <p class="mt-1 text-sm text-slate-600">Department codes distinguish units with the same name in different locations.</p>
            </div>
            <form method="POST" action="{{ route('admin.departments.store') }}" class="space-y-5 p-6">
                @csrf
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <label class="md:col-span-2"><span class="text-sm font-semibold text-slate-800">Department name</span><input name="name" value="{{ old('name') }}" required maxlength="150" placeholder="e.g. Finance Department" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></label>
                    <label><span class="text-sm font-semibold text-slate-800">Code</span><input name="code" value="{{ old('code') }}" required maxlength="20" placeholder="e.g. FINANCE" class="mt-2 block w-full rounded-lg border-slate-300 uppercase text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></label>
                    <label><span class="text-sm font-semibold text-slate-800">Location</span><input name="location" value="{{ old('location', 'Head Office') }}" required maxlength="100" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></label>
                    <label class="md:col-span-2"><span class="text-sm font-semibold text-slate-800">Department head name(s) <span class="font-normal text-slate-400">(optional)</span></span><input name="department_head" value="{{ old('department_head') }}" maxlength="150" placeholder="Add the name later, or separate two names with a comma" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></label>
                    <label><span class="text-sm font-semibold text-slate-800">Number of heads</span><select name="department_head_count" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"><option value="1" @selected((int) old('department_head_count', 1) === 1)>1 head</option><option value="2" @selected((int) old('department_head_count') === 2)>2 heads</option></select></label>
                    <label class="flex items-end gap-2 pb-2 text-sm font-medium text-slate-700"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Department is active</label>
                </div>
                <div class="flex justify-end border-t border-slate-100 pt-5"><button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Save department</button></div>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Department directory</h2>
                    <p class="mt-1 text-sm text-slate-600">Inactive departments are kept for record history and cannot be removed accidentally.</p>
                </div>
                <form method="GET" class="flex flex-col gap-2 sm:flex-row">
                    <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, code, location…" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <select name="status" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"><option value="">All statuses</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option></select>
                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Apply</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-4">Department</th><th class="px-6 py-4">Location</th><th class="px-6 py-4">Department head(s)</th><th class="px-6 py-4">Employees</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Action</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($departments as $department)
                            <tr class="align-top">
                                <td class="px-6 py-4"><p class="font-semibold text-slate-900">{{ $department->name }}</p><span class="mt-2 inline-flex rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-700">{{ $department->code ?? 'No code' }}</span></td>
                                <td class="px-6 py-4 text-slate-600">{{ $department->location }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $department->department_head ?? ($department->department_head_count === 2 ? '2 heads — names pending' : 'Name pending') }}</td>
                                <td class="px-6 py-4"><span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $department->employees_count }} employees</span></td>
                                <td class="px-6 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $department->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $department->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td class="px-6 py-4 text-right">
                                    <details class="relative inline-block text-left"><summary class="cursor-pointer list-none rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Manage</summary>
                                        <div class="absolute right-0 z-30 mt-2 w-[min(34rem,85vw)] rounded-xl border border-slate-200 bg-white p-5 shadow-xl">
                                            <form method="POST" action="{{ route('admin.departments.update', $department) }}" class="grid gap-4 sm:grid-cols-2">@csrf @method('PATCH')
                                                <label class="sm:col-span-2"><span class="text-xs font-semibold text-slate-600">Department name</span><input name="name" value="{{ $department->name }}" required maxlength="150" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"></label>
                                                <label><span class="text-xs font-semibold text-slate-600">Code</span><input name="code" value="{{ $department->code }}" required maxlength="20" class="mt-1 block w-full rounded-lg border-slate-300 uppercase text-sm focus:border-blue-500 focus:ring-blue-500"></label>
                                                <label><span class="text-xs font-semibold text-slate-600">Location</span><input name="location" value="{{ $department->location }}" required maxlength="100" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"></label>
                                                <label class="sm:col-span-2"><span class="text-xs font-semibold text-slate-600">Department head name(s) <span class="font-normal text-slate-400">(optional)</span></span><input name="department_head" value="{{ $department->department_head }}" maxlength="150" placeholder="Separate two names with a comma" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"></label>
                                                <label><span class="text-xs font-semibold text-slate-600">Number of heads</span><select name="department_head_count" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"><option value="1" @selected($department->department_head_count === 1)>1 head</option><option value="2" @selected($department->department_head_count === 2)>2 heads</option></select></label>
                                                <label class="sm:col-span-2 flex items-center gap-2 text-sm font-medium text-slate-700"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($department->is_active) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Department is active</label>
                                                <div class="sm:col-span-2 flex justify-end"><button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Save changes</button></div>
                                            </form>
                                            <div class="mt-5 border-t border-slate-200 pt-5">
                                                <div class="flex items-start justify-between gap-4"><div><h3 class="text-sm font-semibold text-slate-900">People in this department</h3><p class="mt-1 text-xs text-slate-500">Add an employee here, or move them from another department.</p></div><span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $department->employees_count }} employees</span></div>
                                                <form method="POST" action="{{ route('admin.departments.employees.store', $department) }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
                                                    @csrf
                                                    <select name="employee_id" @disabled(! $department->is_active) class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                                        <option value="">Choose an active employee</option>
                                                        @foreach ($employees as $employee)
                                                            @continue($employee->department_id === $department->id)
                                                            <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }} · {{ $employee->employee_number }}@if ($employee->department) · {{ $employee->department->code ?? $employee->department->name }}@endif</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" @disabled(! $department->is_active) class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300">Add employee</button>
                                                </form>
                                                @if (! $department->is_active)<p class="mt-2 text-xs text-amber-700">Activate this department before adding employees.</p>@endif
                                                <div class="mt-4 max-h-40 divide-y divide-slate-100 overflow-y-auto rounded-lg border border-slate-200">
                                                    @forelse ($department->employees as $departmentEmployee)
                                                        <div class="flex items-center justify-between gap-3 px-3 py-2 text-sm"><span class="font-medium text-slate-800">{{ $departmentEmployee->first_name }} {{ $departmentEmployee->last_name }}</span><span class="shrink-0 font-mono text-xs text-slate-500">{{ $departmentEmployee->employee_number }}</span></div>
                                                    @empty
                                                        <p class="px-3 py-3 text-sm text-slate-500">No employees assigned yet.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">No departments match the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($departments->hasPages())<div class="border-t border-slate-200 px-6 py-4">{{ $departments->links() }}</div>@endif
        </section>
    </div>
</x-dashboard-layout>
