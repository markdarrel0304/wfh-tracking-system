<div class="bg-white rounded-lg shadow border border-slate-200 p-4">
    {{-- Month Navigation --}}
    <div class="flex items-center justify-between mb-4">
        <button class="p-1 hover:bg-slate-100 rounded transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <h3 class="text-lg font-semibold text-slate-800">{{ $monthName }}</h3>
        <button class="p-1 hover:bg-slate-100 rounded transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    {{-- Day Headers --}}
    <div class="grid grid-cols-7 gap-1 mb-2">
        <div class="text-center text-xs font-medium text-slate-500 py-2">Su</div>
        <div class="text-center text-xs font-medium text-slate-500 py-2">Mo</div>
        <div class="text-center text-xs font-medium text-slate-500 py-2">Tu</div>
        <div class="text-center text-xs font-medium text-slate-500 py-2">We</div>
        <div class="text-center text-xs font-medium text-slate-500 py-2">Th</div>
        <div class="text-center text-xs font-medium text-slate-500 py-2">Fr</div>
        <div class="text-center text-xs font-medium text-slate-500 py-2">Sa</div>
    </div>

    {{-- Calendar Grid --}}
    <div class="grid grid-cols-7 gap-1">
        @foreach ($calendarDays as $day)
            <div class="text-center py-2 {{ $day['isCurrentMonth'] ? 'text-slate-800' : 'text-slate-300' }} {{ $day['isSelected'] ? 'bg-blue-500 text-white rounded-full' : '' }} {{ $day['isToday'] && !$day['isSelected'] ? 'bg-blue-100 text-blue-600 rounded-full' : '' }} {{ $day['isCurrentMonth'] ? 'hover:bg-slate-100 cursor-pointer' : '' }} rounded-lg transition">
                {{ $day['day'] }}
            </div>
        @endforeach
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-between mt-4 pt-4 border-t border-slate-200">
        <button class="text-sm text-slate-600 hover:text-slate-800 transition">Clear</button>
        <button class="text-sm text-blue-600 hover:text-blue-800 transition">Today</button>
    </div>
</div>