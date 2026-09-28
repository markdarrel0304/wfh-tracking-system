<x-dashboard-layout title="Roles Management">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="text-xs font-bold tracking-[0.18em] text-blue-600">ADMINISTRATION</p><h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Roles and access</h1><p class="mt-1 text-sm text-slate-500">Understand what each fixed role can access before assigning it to a user.</p></div>
            <a href="{{ route('admin.users') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Manage users</a>
        </div>

        <section class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-900"><span class="font-semibold">Fixed roles for safer access.</span> Employee, Supervisor, and Administrator permissions are built into the system. Change a user’s role from Users Management; custom roles are intentionally not available.</section>

        <div class="grid gap-5 lg:grid-cols-3">
            @foreach ($roles as $role)
                @php
                    $style = match ($role['key']) {
                        'employee' => ['border-slate-200', 'bg-slate-50', 'text-slate-700', 'bg-slate-900'],
                        'supervisor' => ['border-amber-200', 'bg-amber-50', 'text-amber-800', 'bg-amber-600'],
                        default => ['border-blue-200', 'bg-blue-50', 'text-blue-800', 'bg-blue-600'],
                    };
                @endphp
                <article class="overflow-hidden rounded-2xl border bg-white shadow-sm {{ $style[0] }}">
                    <div class="{{ $style[1] }} border-b {{ $style[0] }} p-6"><div class="flex items-start justify-between gap-4"><div><h2 class="text-xl font-bold text-slate-950">{{ $role['label'] }}</h2><p class="mt-2 text-sm leading-6 {{ $style[2] }}">{{ $role['description'] }}</p></div><span class="flex h-10 min-w-10 items-center justify-center rounded-xl text-sm font-bold text-white {{ $style[3] }}">{{ $role['total_users'] }}</span></div><div class="mt-5 flex gap-3 text-xs font-semibold {{ $style[2] }}"><span>{{ $role['total_users'] }} assigned</span><span>•</span><span>{{ $role['active_users'] }} active</span></div></div>
                    <div class="p-6"><p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Included permissions</p><ul class="mt-4 space-y-3">@foreach ($role['permissions'] as $permission)<li class="flex gap-3 text-sm leading-5 text-slate-700"><span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">✓</span><span>{{ $permission }}</span></li>@endforeach</ul></div>
                    <div class="border-t {{ $style[0] }} px-6 py-4"><a href="{{ route('admin.users', ['role' => $role['key']]) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">View assigned users →</a></div>
                </article>
            @endforeach
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-bold text-slate-900">Role assignments</h2><p class="mt-1 text-sm text-slate-500">Current accounts grouped by their role. Inactive accounts cannot sign in.</p></div><div class="grid divide-y divide-slate-100 lg:grid-cols-3 lg:divide-x lg:divide-y-0">
            @foreach ($roles as $role)
                <div class="p-6"><div class="flex items-center justify-between"><h3 class="font-bold text-slate-900">{{ $role['label'] }}</h3><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $role['total_users'] }}</span></div><div class="mt-4 space-y-3">@forelse ($recentUsersByRole->get($role['key'], collect()) as $user)<div class="flex items-center justify-between gap-3"><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-800">{{ $user->name }}</p><p class="truncate text-xs text-slate-500">{{ $user->email }}</p></div><span class="shrink-0 rounded-full px-2 py-1 text-xs font-medium {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></div>@empty<p class="text-sm text-slate-500">No users assigned yet.</p>@endforelse</div></div>
            @endforeach
        </div></section>

        <section class="rounded-2xl bg-slate-900 p-6 text-white shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-300">Access safety</p><h2 class="mt-3 text-xl font-bold">Administrator access is protected.</h2><p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">The system will not allow the final active administrator to be deactivated or changed to another role. All role changes are also recorded in Audit Logs.</p></section>
    </div>
</x-dashboard-layout>
