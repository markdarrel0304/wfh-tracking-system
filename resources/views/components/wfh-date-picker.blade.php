@php
use Carbon\Carbon;
@endphp

<div class="relative wfh-date-picker" style="position: relative; z-index: 999999;">
    <div class="relative">
        {{-- Input Field --}}
        <div class="relative">
            <input
                type="text"
                name=""
                value="{{ $selectedDate ? Carbon::parse($selectedDate)->format('m/d/Y') : '' }}"
                {{ $required ? 'required' : '' }}
                readonly
                placeholder="mm/dd/yyyy"
                class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer bg-white date-input"
                data-name="{{ $name }}"
            >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>

        {{-- Hidden input for form submission --}}
        <input type="hidden" name="{{ $name }}" value="{{ $selectedDate ?? '' }}" class="hidden-date-input">

        {{-- Calendar Dropdown --}}
        <div
            class="calendar-dropdown absolute z-[999999] mt-2 {{ $showQuickSelect ? 'w-[500px]' : 'w-64' }} bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden hidden"
        >
            <div class="flex">
                {{-- Left Side - Calendar --}}
                <div class="{{ $showQuickSelect ? 'w-2/3 border-r border-slate-200' : 'w-full' }} p-4">
                    <h5 class="text-sm font-semibold text-slate-700 mb-4">{{ $title }}</h5>
                    {{-- Month/Year Header --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <select onchange="window.wfhDatePickerRender(this.closest('.wfh-date-picker'))" class="year-select text-sm font-semibold text-slate-800 bg-transparent border-none focus:outline-none cursor-pointer">
                                @foreach ($years as $y)
                                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                            <select onchange="window.wfhDatePickerRender(this.closest('.wfh-date-picker'))" class="month-select text-sm font-semibold text-slate-800 bg-transparent border-none focus:outline-none cursor-pointer">
                                @foreach ($months as $num => $name)
                                    <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Day Headers --}}
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        <div class="text-center text-xs font-medium text-slate-500 py-1">Su</div>
                        <div class="text-center text-xs font-medium text-slate-500 py-1">Mo</div>
                        <div class="text-center text-xs font-medium text-slate-500 py-1">Tu</div>
                        <div class="text-center text-xs font-medium text-slate-500 py-1">We</div>
                        <div class="text-center text-xs font-medium text-slate-500 py-1">Th</div>
                        <div class="text-center text-xs font-medium text-slate-500 py-1">Fr</div>
                        <div class="text-center text-xs font-medium text-slate-500 py-1">Sa</div>
                    </div>

                    {{-- Calendar Grid --}}
                    <div class="grid grid-cols-7 gap-1 mb-4 calendar-grid">
                        @foreach ($calendarDays as $day)
                            <button
                                type="button"
                                data-date="{{ $day['date'] }}"
                                class="date-btn text-center py-2 cursor-pointer rounded transition text-sm {{ $day['isCurrentMonth'] ? 'text-slate-800 hover:bg-slate-100' : 'text-slate-300' }} {{ $day['isSelected'] ? 'bg-blue-500 text-white hover:bg-blue-600' : '' }} {{ $day['isToday'] && !$day['isSelected'] ? 'bg-blue-100 text-blue-600 hover:bg-blue-200' : '' }}"
                            >
                                {{ $day['day'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Right Side - Quick Select --}}
                @if ($showQuickSelect)
                <div class="w-1/3 p-4 bg-slate-50">
                    <h5 class="text-sm font-semibold text-slate-700 mb-3">Quick Select</h5>
                    <div class="space-y-2">
                        <button
                            type="button"
                            data-date="{{ Carbon::now()->format('Y-m-d') }}"
                            class="quick-btn block w-full text-left px-3 py-2 text-sm text-slate-600 hover:bg-white hover:text-blue-600 rounded transition"
                        >
                            Today
                        </button>
                        <button
                            type="button"
                            data-date="{{ Carbon::now()->addDays(7)->format('Y-m-d') }}"
                            class="quick-btn block w-full text-left px-3 py-2 text-sm text-slate-600 hover:bg-white hover:text-blue-600 rounded transition"
                        >
                            Next 7 Days
                        </button>
                        <button
                            type="button"
                            data-date="{{ Carbon::now()->addDays(30)->format('Y-m-d') }}"
                            class="quick-btn block w-full text-left px-3 py-2 text-sm text-slate-600 hover:bg-white hover:text-blue-600 rounded transition"
                        >
                            Next 30 Days
                        </button>
                        <button
                            type="button"
                            data-date="{{ Carbon::now()->startOfMonth()->format('Y-m-d') }}"
                            class="quick-btn block w-full text-left px-3 py-2 text-sm text-slate-600 hover:bg-white hover:text-blue-600 rounded transition"
                        >
                            This Month
                        </button>
                        <button
                            type="button"
                            data-date="{{ Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d') }}"
                            class="quick-btn block w-full text-left px-3 py-2 text-sm text-slate-600 hover:bg-white hover:text-blue-600 rounded transition"
                        >
                            Next Month
                        </button>
                    </div>
                </div>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-between px-4 py-3 border-t border-slate-200 bg-white">
                <button
                    type="button"
                    class="close-btn text-sm text-slate-600 hover:text-slate-800 transition"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const datePickers = document.querySelectorAll('.wfh-date-picker');
    
    datePickers.forEach(picker => {
        if (picker.dataset.initialized) {
            return;
        }

        picker.dataset.initialized = 'true';

        const input = picker.querySelector('.date-input');
        const hiddenInput = picker.querySelector('.hidden-date-input');
        const dropdown = picker.querySelector('.calendar-dropdown');
        const quickBtns = picker.querySelectorAll('.quick-btn');
        const closeBtn = picker.querySelector('.close-btn');
        const calendarGrid = picker.querySelector('.calendar-grid');
        
        // Toggle dropdown on input click
        input.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });
        
        calendarGrid.addEventListener('click', function(e) {
            const button = e.target.closest('.date-btn');

            if (!button) {
                return;
            }

            e.stopPropagation();
            const date = button.dataset.date;
            hiddenInput.value = date;
            input.value = formatDate(date);
            dropdown.classList.add('hidden');
        });
        
        // Quick select
        quickBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const date = this.dataset.date;
                hiddenInput.value = date;
                input.value = formatDate(date);
                dropdown.classList.add('hidden');
            });
        });
        
        // Close button
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.add('hidden');
        });

    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        datePickers.forEach(picker => {
            const dropdown = picker.querySelector('.calendar-dropdown');
            if (!dropdown.classList.contains('hidden') && !picker.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    });
    
    function formatDate(dateString) {
        const [year, month, day] = dateString.split('-');
        return `${month}/${day}/${year}`;
    }

})();

window.wfhDatePickerRender = function(picker) {
    const year = Number(picker.querySelector('.year-select').value);
    const month = Number(picker.querySelector('.month-select').value) - 1;
    const calendarGrid = picker.querySelector('.calendar-grid');
    const selectedDate = picker.querySelector('.hidden-date-input').value;
    const today = new Date();
    const firstDay = new Date(year, month, 1);
    const firstVisibleDate = new Date(year, month, 1 - firstDay.getDay());

    calendarGrid.replaceChildren();

    for (let index = 0; index < 42; index++) {
        const date = new Date(firstVisibleDate);
        date.setDate(firstVisibleDate.getDate() + index);

        const dateString = formatDateValue(date);
        const isCurrentMonth = date.getMonth() === month;
        const isSelected = dateString === selectedDate;
        const isToday = dateString === formatDateValue(today);
        const button = document.createElement('button');

        button.type = 'button';
        button.dataset.date = dateString;
        button.textContent = date.getDate();
        button.className = [
            'date-btn',
            'text-center',
            'py-2',
            'cursor-pointer',
            'rounded',
            'transition',
            'text-sm',
            isCurrentMonth ? 'text-slate-800 hover:bg-slate-100' : 'text-slate-300',
            isSelected ? 'bg-blue-500 text-white hover:bg-blue-600' : '',
            isToday && !isSelected ? 'bg-blue-100 text-blue-600 hover:bg-blue-200' : '',
        ].filter(Boolean).join(' ');

        calendarGrid.append(button);
    }
};

function formatDateValue(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}
</script>
