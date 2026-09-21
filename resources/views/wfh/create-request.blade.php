@php
use Carbon\Carbon;
@endphp

<x-dashboard-layout title="WFH Request">
    <div class="mx-auto max-w-6xl">
        <div class="mb-8 flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-blue-600">Remote work</p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">New WFH request</h1>
                <p class="mt-2 text-sm text-slate-600">Tell us when you will be working remotely and submit it for approval.</p>
            </div>
            <a href="{{ route('wfh.my-requests') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">View my requests</a>
        </div>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-5 sm:px-8">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white">1</span>
                        <div>
                            <h2 class="font-semibold text-slate-900">Request information</h2>
                            <p class="text-sm text-slate-500">Complete the details below to start your request.</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('wfh.requests.store') }}" enctype="multipart/form-data" class="space-y-8 px-6 py-7 sm:px-8">
                    @csrf

                    @if ($errors->any())
                        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <p class="font-semibold">Your request could not be submitted.</p>
                            <ul class="mt-2 list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label for="request_type" class="mb-2 block text-sm font-semibold text-slate-800">Work arrangement <span class="text-red-500">*</span></label>
                        <select id="request_type" name="request_type" required class="block w-full max-w-md rounded-lg border-slate-300 py-3 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500">
                            <option value="Work From Home" @selected(old('request_type', 'Work From Home') === 'Work From Home')>Work From Home</option>
                            <option value="Remote Work" @selected(old('request_type') === 'Remote Work')>Remote Work</option>
                            <option value="Hybrid Work" @selected(old('request_type') === 'Hybrid Work')>Hybrid Work</option>
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Select the arrangement that matches your planned workday.</p>
                    </div>

                    <div class="border-t border-slate-100 pt-7">
                        <div class="mb-5 flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-sm font-bold text-blue-700">2</span>
                            <div><h3 class="font-semibold text-slate-900">Schedule</h3><p class="text-sm text-slate-500">Choose the dates and working hours for this request.</p></div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">Start date <span class="text-red-500">*</span></label>
                                <x-wfh-date-picker name="date_from" selectedDate="{{ old('date_from') }}" title="Select start date" :showQuickSelect="false" :required="true" route="{{ route('wfh.requests.create') }}" year="{{ $year ?? Carbon::now()->year }}" month="{{ $month ?? Carbon::now()->month }}" :years="range(Carbon::now()->year, Carbon::now()->year + 1)" :months="[1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December']" :calendarDays="$calendarDays ?? []" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">End date <span class="text-red-500">*</span></label>
                                <x-wfh-date-picker name="date_to" selectedDate="{{ old('date_to') }}" title="Select end date" :showQuickSelect="false" :required="true" route="{{ route('wfh.requests.create') }}" year="{{ $year ?? Carbon::now()->year }}" month="{{ $month ?? Carbon::now()->month }}" :years="range(Carbon::now()->year, Carbon::now()->year + 1)" :months="[1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December']" :calendarDays="$calendarDays ?? []" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">Start time <span class="text-red-500">*</span></label>
                                <x-wfh-time-picker name="start_time" :value="old('start_time')" :required="true" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">End time <span class="text-red-500">*</span></label>
                                <x-wfh-time-picker name="end_time" :value="old('end_time')" :required="true" />
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-7">
                        <div class="mb-5 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-sm font-bold text-blue-700">3</span>
                                <div><label for="reason" class="font-semibold text-slate-900">Reason or purpose <span class="text-red-500">*</span></label><p class="text-sm text-slate-500">Keep this short and specific for faster approval.</p></div>
                            </div>
                            <span id="charCount" class="shrink-0 text-xs text-slate-500">0 / 500</span>
                        </div>
                        <textarea name="reason" id="reason" rows="5" maxlength="500" required class="block w-full resize-none rounded-lg border-slate-300 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500" placeholder="Briefly explain why you need to work from home.">{{ old('reason') }}</textarea>
                    </div>

                    <div class="border-t border-slate-100 pt-7">
                        <label for="supporting_document" class="mb-2 block text-sm font-semibold text-slate-800">Supporting document <span class="font-normal text-slate-400">(optional)</span></label>
                        <p class="mb-3 text-xs text-slate-500">Attach any document that supports your request. PDF, DOC, DOCX, JPG, or PNG up to 2 MB.</p>
                        <input id="supporting_document" type="file" name="supporting_document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="block w-full rounded-lg border border-slate-300 bg-slate-50 text-sm text-slate-600 file:mr-4 file:border-0 file:bg-blue-600 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-700">
                    </div>

                    <div class="flex flex-col-reverse gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs leading-5 text-slate-500">Your request will be sent to your supervisor for review.</p>
                        <button type="submit" class="inline-flex justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Submit request</button>
                    </div>
                </form>
            </section>

            <aside class="space-y-5 lg:sticky lg:top-6">
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="bg-slate-900 px-5 py-4"><h2 class="font-semibold text-white">WFH guidelines</h2><p class="mt-1 text-sm text-slate-300">Please check these before submitting.</p></div>
                    <ul class="divide-y divide-slate-100 px-5">
                        <li class="flex gap-3 py-4 text-sm leading-5 text-slate-700"><span class="font-bold text-blue-600">01</span><span>Submit requests at least <strong>2 working days</strong> in advance.</span></li>
                        <li class="flex gap-3 py-4 text-sm leading-5 text-slate-700"><span class="font-bold text-blue-600">02</span><span>You may request up to <strong>3 WFH days</strong> per week.</span></li>
                        <li class="flex gap-3 py-4 text-sm leading-5 text-slate-700"><span class="font-bold text-blue-600">03</span><span>Stay reachable on chat or video throughout working hours.</span></li>
                        <li class="flex gap-3 py-4 text-sm leading-5 text-slate-700"><span class="font-bold text-blue-600">04</span><span>Ensure your workspace and internet connection are ready.</span></li>
                    </ul>
                </section>
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-5"><p class="text-sm leading-5 text-blue-900"><strong>What happens next?</strong><br>Your supervisor will review the request and you can track its status under My Requests.</p></div>
            </aside>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reasonTextarea = document.getElementById('reason');
            const charCount = document.getElementById('charCount');

            if (reasonTextarea && charCount) {
                reasonTextarea.addEventListener('input', function() {
                    charCount.textContent = this.value.length + ' / 500';
                });
            }
        });
    </script>
    @endpush
</x-dashboard-layout>
