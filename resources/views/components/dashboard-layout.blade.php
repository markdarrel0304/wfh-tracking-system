@props(['title' => 'Dashboard'])

@php
    $isAdmin = auth()->user()->role === 'admin';
    $isEmployeeNavigationMode = $isAdmin && session('navigation_mode') === 'employee';
    $showAdminNavigation = $isAdmin && ! $isEmployeeNavigationMode;
    $showApprovalNavigation = ! $isEmployeeNavigationMode && in_array(auth()->user()->role, ['admin', 'supervisor'], true);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-hidden">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'WFH Tracking System') }} - {{ $title }}</title>

        <script>
            if (localStorage.getItem('theme') === 'dark' || (! localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full overflow-hidden bg-slate-100 font-sans antialiased text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        <div class="fixed inset-0 flex overflow-hidden">
            {{-- Sidebar --}}
            <aside id="workspace-sidebar" class="hidden h-full w-72 shrink-0 flex-col border-r border-slate-800 bg-slate-950 text-white shadow-xl md:flex">
                {{-- Logo --}}
                <div class="shrink-0 border-b border-slate-800 px-5 py-5">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 shadow-lg shadow-blue-950/40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.5 1.5 0 012.122 0L22.28 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </span>
                        <span>
                            <span class="block text-sm font-bold tracking-tight text-white">WFH Tracker</span>
                            <span class="mt-0.5 block text-xs font-medium text-slate-400">Workforce workspace</span>
                        </span>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav id="primary-navigation" class="min-h-0 flex-1 space-y-7 overflow-y-auto overscroll-y-contain px-3 py-5">
                    {{-- Dashboard --}}
                    @if (! $showAdminNavigation)
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Main</h3>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                    </svg>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('my-profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('my-profile') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                    <span>My Profile</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('my-work-schedule') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('my-work-schedule') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                    <span>My Schedule</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    @if ($showAdminNavigation)
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Main</h3>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                    </svg>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    {{-- Employees (Admin Only) --}}
                    @if ($showAdminNavigation)
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Employees</h3>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('employees.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('employees.index') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                    </svg>
                                    <span>Employee List</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    {{-- WFH Management --}}
                    @if (! $showAdminNavigation)
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">WFH Management</h3>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('wfh.requests.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('wfh.requests.create') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    <span>WFH Request</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('wfh.my-requests') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('wfh.my-requests') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    <span>My Requests</span>
                                </a>
                            </li>
                            @if ($showApprovalNavigation)
                            <li>
                                <a href="{{ route('wfh.approval') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('wfh.approval*') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Approval</span>
                                </a>
                            </li>
                            @endif
                            <li>
                                <a href="{{ route('wfh.calendar') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('wfh.calendar') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                    <span>WFH Calendar</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    {{-- Attendance --}}
                    @if (! $showAdminNavigation)
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Attendance</h3>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('attendance.time-in-out') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('attendance.time-in-out') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 3.03v.75m0-2.25v.75m0 9.75v.75m0-2.25v.75m-9 9.75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                    </svg>
                                    <span>Time In/Out</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('attendance.daily') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('attendance.daily') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                    <span>Daily Attendance</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('attendance.corrections') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('attendance.corrections') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                    <span>Attendance Corrections</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    {{-- Accomplishments --}}
                    @if (! $showAdminNavigation)
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Accomplishments</h3>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('accomplishments.reports') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('accomplishments.reports') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Accomplishments</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    {{-- Approvals (Admin and Supervisor) --}}
                    @if ($showApprovalNavigation)
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Approvals</h3>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('approvals.wfh-requests') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('approvals.wfh-requests') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>WFH Requests</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('approvals.attendance-corrections') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('approvals.attendance-corrections*') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                    <span>Attendance Corrections</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('approvals.accomplishment-reports') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('approvals.accomplishment-reports') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    <span>Accomplishment Reports</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    {{-- Reports (Admin Only) --}}
                    @if ($showAdminNavigation)
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Reports</h3>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('reports.attendance') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('reports.attendance') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                    </svg>
                                    <span>Attendance</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.wfh') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('reports.wfh') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                                    </svg>
                                    <span>WFH</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.accomplishments') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('reports.accomplishments') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                                    </svg>
                                    <span>Accomplishments</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.payroll') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('reports.payroll') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 15.797c.71.71 1.185 1.622 1.185 2.696V21a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18.75v-1.283c0-1.074.474-1.986 1.185-2.696A60.075 60.075 0 0120.25 3" />
                                    </svg>
                                    <span>Payroll/Timekeeping Export</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    {{-- System (Admin Only) --}}
                    @if ($showAdminNavigation)
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">System</h3>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('audit-logs.index') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Audit Logs</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    {{-- Administration (Admin Only) --}}
                    @if ($showAdminNavigation)
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Administration</h3>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('admin.users') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                    </svg>
                                    <span>Users</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.roles') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('admin.roles') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Roles</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.departments') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('admin.departments') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                                    </svg>
                                    <span>Departments</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.holidays') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('admin.holidays') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
                                    </svg>
                                    <span>Holidays</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif
                </nav>

                {{-- User info --}}
                <div id="sidebar-account" class="mx-3 mb-3 shrink-0 rounded-xl border border-slate-800 bg-slate-900/70 p-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-slate-400 hover:text-white transition" title="Sign out" aria-label="Sign out">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                            </button>
                        </form>
                    </div>
                    @if ($isAdmin)
                        <form method="POST" action="{{ route('navigation-mode.update') }}" class="mt-3">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="mode" value="{{ $isEmployeeNavigationMode ? 'admin' : 'employee' }}">
                            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-700 px-3 py-2 text-xs font-semibold text-slate-300 transition hover:border-slate-600 hover:bg-slate-800 hover:text-white">
                                @if ($isEmployeeNavigationMode)
                                    <span>Return to admin view</span>
                                @else
                                    <span>View as employee</span>
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            </aside>

            {{-- Main content --}}
            <main class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
                {{-- Top bar --}}
                <header class="sticky top-0 z-30 flex shrink-0 items-center justify-between border-b border-slate-200 bg-white/95 px-6 py-4 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/95">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-blue-600">Workspace</p>
                        <h1 class="mt-0.5 text-xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <button type="button" id="theme-toggle" class="flex h-10 w-10 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Switch to dark mode" title="Switch to dark mode">
                            <svg class="theme-icon-light h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5m0 15V21m9-9h-1.5M4.5 12H3m15.364-6.364l-1.06 1.06M6.696 17.304l-1.06 1.06m12.728 0l-1.06-1.06M6.696 6.696l-1.06-1.06M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <svg class="theme-icon-dark hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" /></svg>
                        </button>
                        <details class="group relative">
                            <summary class="relative flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 [&::-webkit-details-marker]:hidden" aria-label="Open notifications">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                                @if ($unreadNotificationCount > 0)
                                    <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ min($unreadNotificationCount, 9) }}</span>
                                @endif
                            </summary>

                            <div class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
                                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                    <div>
                                        <p class="font-semibold text-slate-800">Notifications</p>
                                        <p class="text-xs text-slate-500">{{ $unreadNotificationCount }} unread</p>
                                    </div>
                                    <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">View all</a>
                                </div>

                                <div class="max-h-96 overflow-y-auto">
                                    @forelse ($headerNotifications as $notification)
                                        <a href="{{ route('notifications.show', $notification) }}" class="flex gap-3 border-b border-slate-100 px-4 py-3 transition hover:bg-slate-50">
                                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-blue-600"></span>
                                            <span class="min-w-0">
                                                <span class="block text-sm font-semibold text-slate-800">{{ $notification->data['title'] ?? 'New notification' }}</span>
                                                <span class="mt-0.5 block text-xs leading-5 text-slate-600">{{ $notification->data['message'] ?? '' }}</span>
                                                <span class="mt-1 block text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                            </span>
                                        </a>
                                    @empty
                                        <div class="px-4 py-10 text-center">
                                            <p class="text-sm font-semibold text-slate-700">You are all caught up</p>
                                            <p class="mt-1 text-xs text-slate-500">New activity will appear here.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </details>
                    </div>
                </header>

                {{-- Page content --}}
                <div class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain p-6">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @stack('styles')
        @stack('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const themeToggle = document.getElementById('theme-toggle');
                const lightIcon = themeToggle?.querySelector('.theme-icon-light');
                const darkIcon = themeToggle?.querySelector('.theme-icon-dark');

                const updateThemeToggle = () => {
                    const isDark = document.documentElement.classList.contains('dark');

                    lightIcon?.classList.toggle('hidden', isDark);
                    darkIcon?.classList.toggle('hidden', ! isDark);
                    themeToggle?.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
                    themeToggle?.setAttribute('title', isDark ? 'Switch to light mode' : 'Switch to dark mode');
                };

                updateThemeToggle();
                themeToggle?.addEventListener('click', () => {
                    document.documentElement.classList.toggle('dark');
                    localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
                    updateThemeToggle();
                });

                const navigation = document.getElementById('primary-navigation');
                const activeLink = navigation?.querySelector('a.bg-blue-600');

                if (navigation && activeLink) {
                    const navigationBounds = navigation.getBoundingClientRect();
                    const linkBounds = activeLink.getBoundingClientRect();

                    navigation.scrollTop += linkBounds.top - navigationBounds.top
                        - (navigation.clientHeight - linkBounds.height) / 2;
                }
            });
        </script>
    </body>
</html>
