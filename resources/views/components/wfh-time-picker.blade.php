@props(['name', 'value' => null, 'required' => false])

@php
    $time = $value ? \Carbon\Carbon::parse($value) : null;
    $hour = $time?->format('g') ?? '8';
    $minute = $time?->format('i') ?? '00';
    $period = $time?->format('A') ?? 'AM';
    $displayValue = $time?->format('g:i A') ?? '';
@endphp

<div class="relative wfh-time-picker" data-initialized="false">
    <input type="hidden" name="{{ $name }}" value="{{ $value ?? '' }}" class="time-value">
    <div class="relative">
        <input type="text" value="{{ $displayValue }}" {{ $required ? 'required' : '' }} readonly placeholder="Select time" class="time-input block w-full cursor-pointer rounded-lg border-slate-300 py-3 pl-4 pr-10 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">
        <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">◷</span>
    </div>
    <div class="time-dropdown absolute z-[999999] mt-2 hidden w-full min-w-64 rounded-xl border border-slate-200 bg-white p-4 shadow-xl">
        <p class="text-sm font-semibold text-slate-800">Select time</p>
        <div class="mt-4 grid grid-cols-3 gap-3">
            <label class="text-xs font-medium text-slate-600">Hour<select class="time-hour mt-1 block w-full rounded-lg border-slate-300 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">@foreach (range(1, 12) as $option)<option value="{{ $option }}" @selected((string) $option === $hour)>{{ $option }}</option>@endforeach</select></label>
            <label class="text-xs font-medium text-slate-600">Minute<select class="time-minute mt-1 block w-full rounded-lg border-slate-300 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">@foreach (['00', '15', '30', '45'] as $option)<option value="{{ $option }}" @selected($option === $minute)>{{ $option }}</option>@endforeach</select></label>
            <label class="text-xs font-medium text-slate-600">Period<select class="time-period mt-1 block w-full rounded-lg border-slate-300 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"><option value="AM" @selected($period === 'AM')>AM</option><option value="PM" @selected($period === 'PM')>PM</option></select></label>
        </div>
        <div class="mt-4 flex justify-end gap-2 border-t border-slate-100 pt-4"><button type="button" class="time-cancel rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</button><button type="button" class="time-apply rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">Apply</button></div>
    </div>
</div>

<script>
    (function () {
        document.querySelectorAll('.wfh-time-picker').forEach(function (picker) {
            if (picker.dataset.initialized === 'true') {
                return;
            }

            picker.dataset.initialized = 'true';
            const input = picker.querySelector('.time-input');
            const value = picker.querySelector('.time-value');
            const dropdown = picker.querySelector('.time-dropdown');
            const hour = picker.querySelector('.time-hour');
            const minute = picker.querySelector('.time-minute');
            const period = picker.querySelector('.time-period');

            input.addEventListener('click', function (event) {
                event.stopPropagation();
                dropdown.classList.toggle('hidden');
            });

            const saveTime = function () {
                let hourValue = Number(hour.value);
                if (period.value === 'PM' && hourValue !== 12) {
                    hourValue += 12;
                }
                if (period.value === 'AM' && hourValue === 12) {
                    hourValue = 0;
                }

                value.value = `${String(hourValue).padStart(2, '0')}:${minute.value}`;
                input.value = `${hour.value}:${minute.value} ${period.value}`;
            };

            picker.querySelector('.time-apply').addEventListener('click', function () {
                saveTime();
                dropdown.classList.add('hidden');
            });

            [hour, minute, period].forEach(function (control) {
                control.addEventListener('change', saveTime);
            });

            picker.querySelector('.time-cancel').addEventListener('click', function () {
                dropdown.classList.add('hidden');
            });
        });

        document.addEventListener('click', function (event) {
            document.querySelectorAll('.wfh-time-picker').forEach(function (picker) {
                if (! picker.contains(event.target)) {
                    picker.querySelector('.time-dropdown').classList.add('hidden');
                }
            });
        });
    })();
</script>
