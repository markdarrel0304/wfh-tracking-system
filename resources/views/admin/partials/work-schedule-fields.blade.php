@php
    $selectedDays = old('days', $schedule?->days_json ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']);
    $value = static fn (string $field, mixed $fallback = null): mixed => old($field, $schedule?->{$field} ?? $fallback);
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
    <div class="min-w-0 xl:col-span-2">
        <label for="name-{{ $schedule?->id ?? 'new' }}" class="block text-sm font-semibold text-slate-800">Schedule name</label>
        <input id="name-{{ $schedule?->id ?? 'new' }}" name="name" type="text" value="{{ $value('name') }}" placeholder="e.g. Standard office hours" required maxlength="100" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
    </div>

    <fieldset class="min-w-0 xl:col-span-2">
        <legend class="text-sm font-semibold text-slate-800">Working days</legend>
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($weekdays as $day => $label)
                <label class="cursor-pointer">
                    <input name="days[]" value="{{ $day }}" type="checkbox" class="peer sr-only" @checked(in_array($day, $selectedDays, true))>
                    <span class="inline-flex rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-600 transition peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:text-white">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <div>
        <label for="time_in-{{ $schedule?->id ?? 'new' }}" class="block text-sm font-semibold text-slate-800">Regular time in</label>
        <input id="time_in-{{ $schedule?->id ?? 'new' }}" name="time_in" type="time" value="{{ $timeValue($value('time_in', '08:00:00')) }}" required class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div>
        <label for="time_out-{{ $schedule?->id ?? 'new' }}" class="block text-sm font-semibold text-slate-800">Regular time out</label>
        <input id="time_out-{{ $schedule?->id ?? 'new' }}" name="time_out" type="time" value="{{ $timeValue($value('time_out', '17:00:00')) }}" required class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div>
        <label for="overtime_start-{{ $schedule?->id ?? 'new' }}" class="block text-sm font-semibold text-slate-800">Overtime starts</label>
        <input id="overtime_start-{{ $schedule?->id ?? 'new' }}" name="overtime_start" type="time" value="{{ $timeValue($value('overtime_start', '17:30:00')) }}" required class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
    </div>
    <div class="grid grid-cols-2 gap-3 sm:col-span-2">
        <div>
            <label for="overtime_minimum_minutes-{{ $schedule?->id ?? 'new' }}" class="block text-sm font-semibold text-slate-800">Min. OT minutes</label>
            <input id="overtime_minimum_minutes-{{ $schedule?->id ?? 'new' }}" name="overtime_minimum_minutes" type="number" min="1" max="480" value="{{ $value('overtime_minimum_minutes', 120) }}" required class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
            <label for="overtime_maximum_minutes-{{ $schedule?->id ?? 'new' }}" class="block text-sm font-semibold text-slate-800">Max. OT minutes</label>
            <input id="overtime_maximum_minutes-{{ $schedule?->id ?? 'new' }}" name="overtime_maximum_minutes" type="number" min="1" max="480" value="{{ $value('overtime_maximum_minutes', 180) }}" required class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
    </div>
</div>
