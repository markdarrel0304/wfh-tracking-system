<x-dashboard-layout title="Holidays Management">
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Administration</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Holidays</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">Set the dates that employees see on their schedules. Inactive holidays remain in the record but no longer affect schedules.</p>
            </div>
            <a href="#new-holiday" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Add holiday</a>
        </section>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"><p class="font-semibold">Please review the holiday details.</p><ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section id="new-holiday" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-semibold text-slate-950">Add a holiday</h2><p class="mt-1 text-sm text-slate-600">Use the actual calendar date. Mark annual holidays as recurring to keep the record clear.</p></div>
            <form method="POST" action="{{ route('admin.holidays.store') }}" class="space-y-5 p-6">
                @csrf
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <label class="md:col-span-2"><span class="text-sm font-semibold text-slate-800">Holiday name</span><input name="name" value="{{ old('name') }}" required maxlength="150" placeholder="e.g. Foundation Day" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></label>
                    <div><span class="text-sm font-semibold text-slate-800">Date</span><div class="mt-2"><x-wfh-date-picker name="date" :selectedDate="old('date')" title="Select holiday date" :showQuickSelect="false" :required="true" route="{{ route('admin.holidays') }}" :year="$calendarDate->year" :month="$calendarDate->month" :years="$years" :months="$calendarMonths" :calendarDays="$calendarDays" /></div></div>
                    <label><span class="text-sm font-semibold text-slate-800">Holiday type</span><select name="type" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"><option value="regular" @selected(old('type', 'regular') === 'regular')>Regular holiday</option><option value="special" @selected(old('type') === 'special')>Special non-working day</option></select></label>
                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700"><input type="hidden" name="is_recurring" value="0"><input type="checkbox" name="is_recurring" value="1" @checked(old('is_recurring')) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Recurring yearly</label>
                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Active on schedules</label>
                </div>
                <div class="flex justify-end border-t border-slate-100 pt-5"><button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Save holiday</button></div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div><h2 class="text-lg font-semibold text-slate-950">Holiday calendar</h2><p class="mt-1 text-sm text-slate-600">Review and maintain the holidays recorded for {{ $selectedYear }}.</p></div>
                <form method="GET" class="flex flex-col gap-2 sm:flex-row"><select name="year" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">@foreach ($years as $year)<option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>@endforeach</select><select name="type" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"><option value="">All types</option><option value="regular" @selected(($filters['type'] ?? '') === 'regular')>Regular</option><option value="special" @selected(($filters['type'] ?? '') === 'special')>Special</option></select><select name="status" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"><option value="">All statuses</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option></select><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Apply</button></form>
            </div>
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm"><thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-4">Holiday</th><th class="px-6 py-4">Date</th><th class="px-6 py-4">Type</th><th class="px-6 py-4">Schedule use</th><th class="px-6 py-4 text-right">Action</th></tr></thead><tbody class="divide-y divide-slate-100">
                @forelse ($holidays as $holiday)
                    <tr class="align-top"><td class="px-6 py-4 font-semibold text-slate-900">{{ $holiday->name }}@if ($holiday->is_recurring)<span class="ml-2 inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">Recurring</span>@endif</td><td class="px-6 py-4 text-slate-600">{{ $holiday->date->format('D, M j, Y') }}</td><td class="px-6 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $holiday->type === 'regular' ? 'bg-violet-50 text-violet-700' : 'bg-amber-50 text-amber-700' }}">{{ $holiday->type === 'regular' ? 'Regular' : 'Special' }}</span></td><td class="px-6 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $holiday->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $holiday->is_active ? 'Active' : 'Inactive' }}</span></td><td class="px-6 py-4 text-right"><details class="relative inline-block text-left"><summary class="cursor-pointer list-none rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Manage</summary><div class="absolute right-0 z-30 mt-2 w-[min(32rem,85vw)] rounded-xl border border-slate-200 bg-white p-5 shadow-xl"><form method="POST" action="{{ route('admin.holidays.update', $holiday) }}" class="grid gap-4 sm:grid-cols-2">@csrf @method('PATCH')<label class="sm:col-span-2"><span class="text-xs font-semibold text-slate-600">Holiday name</span><input name="name" value="{{ $holiday->name }}" required maxlength="150" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"></label><label><span class="text-xs font-semibold text-slate-600">Date</span><input type="date" name="date" value="{{ $holiday->date->toDateString() }}" required class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"></label><label><span class="text-xs font-semibold text-slate-600">Holiday type</span><select name="type" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"><option value="regular" @selected($holiday->type === 'regular')>Regular holiday</option><option value="special" @selected($holiday->type === 'special')>Special non-working day</option></select></label><label class="flex items-center gap-2 text-sm font-medium text-slate-700"><input type="hidden" name="is_recurring" value="0"><input type="checkbox" name="is_recurring" value="1" @checked($holiday->is_recurring) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Recurring yearly</label><label class="flex items-center gap-2 text-sm font-medium text-slate-700"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($holiday->is_active) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Active on schedules</label><div class="sm:col-span-2 flex justify-end"><button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Save changes</button></div></form></div></details></td></tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">No holidays recorded for {{ $selectedYear }}.</td></tr>
                @endforelse
            </tbody></table></div>
        </section>
    </div>
</x-dashboard-layout>
