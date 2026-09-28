<x-dashboard-layout title="Notifications">
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Activity</p>
                <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Notifications</h2>
                <p class="mt-2 text-sm text-slate-600">Stay updated on your Work From Home requests and approvals.</p>
            </div>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Mark all as read</button>
                </form>
            @endif
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <p class="font-semibold text-slate-800">Recent activity</p>
                <p class="mt-1 text-sm text-slate-500">{{ $unreadCount }} unread notification{{ $unreadCount === 1 ? '' : 's' }}</p>
            </div>

            @forelse ($notifications as $notification)
                <a href="{{ route('notifications.show', $notification) }}" class="flex gap-4 border-b border-slate-100 px-5 py-4 transition hover:bg-slate-50 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/50' }}">
                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $notification->read_at ? 'bg-slate-300' : 'bg-blue-600' }}"></span>
                    <span class="min-w-0 flex-1">
                        <span class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <span class="font-semibold text-slate-800">{{ $notification->data['title'] ?? 'New notification' }}</span>
                            <span class="text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                        </span>
                        <span class="mt-1 block text-sm leading-6 text-slate-600">{{ $notification->data['message'] ?? '' }}</span>
                    </span>
                </a>
            @empty
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <p class="mt-4 font-semibold text-slate-800">No notifications yet</p>
                    <p class="mt-1 text-sm text-slate-500">WFH request updates will appear here.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-dashboard-layout>
