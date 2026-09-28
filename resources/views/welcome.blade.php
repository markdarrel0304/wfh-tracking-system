<x-guest-layout>
    <div class="mx-auto flex min-h-screen max-w-7xl items-center px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid w-full overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900 lg:grid-cols-5">
            <section class="relative overflow-hidden bg-slate-950 px-7 py-10 text-white sm:px-10 lg:col-span-3 lg:flex lg:min-h-[42rem] lg:flex-col lg:px-12">
                <div aria-hidden="true" class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.48),_transparent_42%),radial-gradient(circle_at_bottom_right,_rgba(34,211,238,0.22),_transparent_44%)]"></div>

                <div class="relative flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 shadow-lg shadow-blue-900/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.5 1.5 0 012.122 0L22.28 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </span>
                    <span>
                        <span class="block font-bold tracking-tight">WFH Tracker</span>
                        <span class="block text-xs font-medium text-slate-400">Workforce workspace</span>
                    </span>
                </div>

                <div class="relative py-12 lg:my-auto">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300">Work, made visible</p>
                    <h1 class="mt-4 max-w-xl text-4xl font-bold tracking-tight sm:text-5xl">A clearer way to manage every workday.</h1>
                    <p class="mt-5 max-w-lg text-sm leading-7 text-slate-300 sm:text-base">Track attendance, plan remote work, and document completed tasks in one secure workspace for employees and administrators.</p>

                    <div class="mt-8 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                            <p class="text-xs font-bold uppercase tracking-wider text-cyan-200">Attendance</p>
                            <p class="mt-2 text-sm font-semibold">Time in. Time out.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                            <p class="text-xs font-bold uppercase tracking-wider text-cyan-200">WFH</p>
                            <p class="mt-2 text-sm font-semibold">Plan with confidence.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                            <p class="text-xs font-bold uppercase tracking-wider text-cyan-200">Progress</p>
                            <p class="mt-2 text-sm font-semibold">Keep work documented.</p>
                        </div>
                    </div>
                </div>

                <div class="relative flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-white/10 pt-6 text-xs font-medium text-slate-300">
                    <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>Attendance records</span>
                    <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-blue-400"></span>WFH approvals</span>
                    <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-cyan-300"></span>Task accomplishments</span>
                </div>
            </section>

            <section class="flex items-center px-6 py-10 sm:px-12 lg:col-span-2 lg:px-10">
                <div class="mx-auto w-full max-w-sm">
                    <div class="mb-8 lg:hidden">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.5 1.5 0 012.122 0L22.28 12" /></svg>
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white">WFH Tracker</span>
                        </div>
                    </div>

                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">Workforce workspace</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Welcome back.</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-300">Sign in to manage your workday or create an account to get started.</p>

                    <div class="mt-8 grid gap-3">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                                Sign in
                                <span aria-hidden="true">→</span>
                            </a>
                        @endif

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-600 dark:text-slate-200 dark:hover:border-blue-500 dark:hover:bg-slate-800 dark:hover:text-white dark:focus:ring-offset-slate-900">
                                Create an account
                            </a>
                        @endif
                    </div>

                    <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Everything in one place</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-300">Use your assigned account to access attendance, requests, schedules, and accomplishments.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>
