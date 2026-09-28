<x-dashboard-layout title="Create Work Schedule">
    @php
        $weekdays = [
            'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 'thursday' => 'Thu',
            'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun',
        ];
        $timeValue = static fn (?string $time): string => $time ? substr($time, 0, 5) : '';
    @endphp

    <div class="mx-auto max-w-4xl space-y-6 pb-6">
        <a href="{{ route('admin.work-schedules') }}" class="inline-flex text-sm font-semibold text-blue-600 hover:text-blue-700">← Back to schedule templates</a>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Step 1 of 2</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Create a work schedule</h1>
                <p class="mt-1 text-sm text-slate-600">Set the standard workday first. You will assign employees on the next screen.</p>
            </div>

            @if ($errors->any())
                <div class="mx-6 mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <p class="font-semibold">Please check the schedule details.</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.work-schedules.store') }}" class="space-y-6 p-6">
                @csrf
                @include('admin.partials.work-schedule-fields', ['schedule' => null, 'weekdays' => $weekdays, 'timeValue' => $timeValue])
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.work-schedules') }}" class="inline-flex justify-center rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Save and assign employees</button>
                </div>
            </form>
        </section>
    </div>
</x-dashboard-layout>
