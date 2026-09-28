<div
    class="relative"
    x-data="scheduleDatePicker"
    x-init="initialize('{{ $selectedDate ?? '' }}')"
    data-years='@json($years)'
    data-months='@json($months)'
>
    <div class="relative">
        <input
            type="text"
            name=""
            :value="formatDate(selectedDate)"
            {{ $required ? 'required' : '' }}
            readonly
            @click="isOpen = !isOpen"
            placeholder="mm/dd/yyyy"
            class="w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2 pr-10 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
            aria-label="Open calendar"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </button>
    </div>

    <div
        x-cloak
        x-show="isOpen"
        @click.outside="isOpen = false"
        class="absolute z-50 mt-2 w-80 rounded-lg border border-slate-200 bg-white p-4 shadow-xl"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
    >
        <div class="mb-4 flex items-center justify-between">
            <button type="button" @click="previousMonth()" class="rounded p-1 transition hover:bg-slate-100" aria-label="Previous month">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <div class="flex items-center gap-2">
                <select x-model="month" @change="updateCalendar()" class="cursor-pointer border-none bg-transparent text-sm font-semibold text-slate-800 focus:outline-none">
                    <template x-for="(name, number) in months" :key="number"><option :value="number" x-text="name"></option></template>
                </select>
                <select x-model="year" @change="updateCalendar()" class="cursor-pointer border-none bg-transparent text-sm font-semibold text-slate-800 focus:outline-none">
                    <template x-for="yearOption in years" :key="yearOption"><option :value="yearOption" x-text="yearOption"></option></template>
                </select>
            </div>
            <button type="button" @click="nextMonth()" class="rounded p-1 transition hover:bg-slate-100" aria-label="Next month">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>

        <div class="mb-2 grid grid-cols-7 gap-1">
            <template x-for="dayName in ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']" :key="dayName"><div class="py-1 text-center text-xs font-medium text-slate-500" x-text="dayName"></div></template>
        </div>
        <div class="mb-4 grid grid-cols-7 gap-1">
            <template x-for="(day, index) in calendarDays" :key="`${index}-${day.day}-${day.isCurrentMonth}`">
                <button
                    type="button"
                    class="rounded py-2 text-center text-sm transition"
                    :class="{
                        'cursor-pointer text-slate-800 hover:bg-slate-100': day.isCurrentMonth && !day.isSelected,
                        'cursor-default text-slate-300': !day.isCurrentMonth,
                        'cursor-pointer bg-blue-600 text-white hover:bg-blue-700': day.isSelected,
                        'bg-blue-100 text-blue-700': day.isToday && !day.isSelected
                    }"
                    @click="selectDate(day)"
                    :disabled="!day.isCurrentMonth"
                ><span x-text="day.day"></span></button>
            </template>
        </div>

        <div class="flex items-center justify-between border-t border-slate-200 pt-3">
            <button type="button" @click="selectToday()" class="text-sm font-medium text-blue-600 transition hover:text-blue-800">Today</button>
            <button type="button" @click="isOpen = false" class="text-sm font-medium text-slate-600 transition hover:text-slate-800">Close</button>
        </div>
    </div>

    <input type="hidden" name="{{ $name }}" :value="selectedDate">
</div>
