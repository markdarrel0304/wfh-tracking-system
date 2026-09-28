

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('scheduleDatePicker', () => ({
    isOpen: false,
    selectedDate: '',
    year: new Date().getFullYear(),
    month: new Date().getMonth() + 1,
    calendarDays: [],
    years: [],
    months: {},

    initialize(initialDate) {
        this.years = JSON.parse(this.$el.dataset.years);
        this.months = JSON.parse(this.$el.dataset.months);
        const normalizedInitialDate = initialDate?.split(/[ T]/)[0] ?? '';

        if (normalizedInitialDate) {
            this.selectedDate = normalizedInitialDate;
            const [year, month] = normalizedInitialDate.split('-').map(Number);
            this.year = year;
            this.month = month;
        }

        this.updateCalendar();
    },

    formatDate(date) {
        if (!date) {
            return '';
        }

        const [year, month, day] = date.split('-');

        return `${month}/${day}/${year}`;
    },

    dateString(year, month, day) {
        return `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    },

    updateCalendar() {
        const daysInMonth = new Date(this.year, this.month, 0).getDate();
        const firstDay = new Date(this.year, this.month - 1, 1).getDay();
        const today = new Date();
        const days = [];
        const previousMonth = new Date(this.year, this.month - 1, 0);

        for (let index = firstDay; index > 0; index--) {
            days.push({
                day: previousMonth.getDate() - index + 1,
                isCurrentMonth: false,
                isToday: false,
                isSelected: false,
            });
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(this.year, this.month - 1, day);
            const dateString = this.dateString(this.year, this.month, day);

            days.push({
                day,
                isCurrentMonth: true,
                isToday: date.toDateString() === today.toDateString(),
                isSelected: this.selectedDate === dateString,
            });
        }

        for (let day = 1; day <= 42 - days.length; day++) {
            days.push({
                day,
                isCurrentMonth: false,
                isToday: false,
                isSelected: false,
            });
        }

        this.calendarDays = days;
    },

    previousMonth() {
        this.month--;

        if (this.month < 1) {
            this.month = 12;
            this.year--;
        }

        this.updateCalendar();
    },

    nextMonth() {
        this.month++;

        if (this.month > 12) {
            this.month = 1;
            this.year++;
        }

        this.updateCalendar();
    },

    selectDate(day) {
        if (!day.isCurrentMonth) {
            return;
        }

        this.selectedDate = this.dateString(this.year, this.month, day.day);
        this.isOpen = false;
        this.updateCalendar();
    },

    selectToday() {
        const today = new Date();
        this.year = today.getFullYear();
        this.month = today.getMonth() + 1;
        this.selectedDate = this.dateString(this.year, this.month, today.getDate());
        this.isOpen = false;
        this.updateCalendar();
    },
}));

Alpine.start();
