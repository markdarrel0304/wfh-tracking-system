<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Welcome banner --}}
            <div class="bg-slate-800 rounded-xl p-6 text-white">
                <h3 class="text-lg font-semibold">Welcome back, {{ auth()->user()->name }}!</h3>
                <p class="text-sm text-slate-300 mt-1">Here's what's happening with your WFH schedule today.</p>
            </div>

            {{-- Stat cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Total Employees</p>
                            <p class="text-2xl font-bold text-slate-800 mt-1">{{ \App\Models\Employee::count() }}</p>
                        </div>
                        <div class="bg-blue-50 text-blue-700 rounded-lg p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Pending WFH Requests</p>
                            <p class="text-2xl font-bold text-slate-800 mt-1">{{ \App\Models\WfhRequest::where('status', 'pending')->count() }}</p>
                        </div>
                        <div class="bg-amber-50 text-amber-600 rounded-lg p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Approved This Month</p>
                            <p class="text-2xl font-bold text-slate-800 mt-1">
                                {{ \App\Models\WfhRequest::where('status', 'approved')->whereMonth('created_at', now()->month)->count() }}
                            </p>
                        </div>
                        <div class="bg-green-50 text-green-600 rounded-lg p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Today's Attendance</p>
                            <p class="text-2xl font-bold text-slate-800 mt-1">
                                {{ \App\Models\Attendance::whereDate('date', today())->count() }}
                            </p>
                        </div>
                        <div class="bg-indigo-50 text-indigo-600 rounded-lg p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Two-column: Recent requests + Quick actions --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Recent WFH requests --}}
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-semibold text-slate-800">Recent WFH Requests</h3>
                        <a href="#" class="text-sm text-blue-700 hover:underline">View all</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-500 border-b border-slate-100">
                                    <th class="px-5 py-3 font-medium">Employee</th>
                                    <th class="px-5 py-3 font-medium">Dates</th>
                                    <th class="px-5 py-3 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (\App\Models\WfhRequest::with('employee')->latest()->take(5)->get() as $request)
                                    <tr class="border-b border-slate-50 last:border-0">
                                        <td class="px-5 py-3 text-slate-700">
                                            {{ $request->employee->first_name }} {{ $request->employee->last_name }}
                                        </td>
                                        <td class="px-5 py-3 text-slate-500">
                                            {{ \Carbon\Carbon::parse($request->date_from)->format('M d') }}
                                            &ndash;
                                            {{ \Carbon\Carbon::parse($request->date_to)->format('M d') }}
                                        </td>
                                        <td class="px-5 py-3">
                                            @php
                                                $badge = match($request->status) {
                                                    'approved' => 'bg-green-50 text-green-700',
                                                    'rejected' => 'bg-red-50 text-red-700',
                                                    'cancelled' => 'bg-slate-100 text-slate-500',
                                                    default => 'bg-amber-50 text-amber-700',
                                                };
                                            @endphp
                                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-5 py-6 text-center text-slate-400">No WFH requests yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Quick actions --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <h3 class="font-semibold text-slate-800 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="#" class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                            <span class="bg-blue-50 text-blue-700 rounded-lg p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </span>
                            <span class="text-sm font-medium text-slate-700">New WFH Request</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                            <span class="bg-green-50 text-green-700 rounded-lg p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <span class="text-sm font-medium text-slate-700">Clock In / Out</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                            <span class="bg-indigo-50 text-indigo-700 rounded-lg p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                </svg>
                            </span>
                            <span class="text-sm font-medium text-slate-700">Submit Accomplishment</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>