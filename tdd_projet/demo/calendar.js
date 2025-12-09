// Simple calendar implementation for scheduler demo
class CalendarUI {
    constructor(schedulerUI) {
        this.schedulerUI = schedulerUI;
        this.currentMonth = new Date(schedulerUI.currentTime);
        this.currentMonth.setDate(1);

        this.initializeElements();
        this.attachEventListeners();
        this.render();
    }

    initializeElements() {
        this.calendarTitle = document.getElementById('calendar-title');
        this.calendarDays = document.getElementById('calendar-days');
        this.prevMonthBtn = document.getElementById('prev-month-btn');
        this.nextMonthBtn = document.getElementById('next-month-btn');
    }

    attachEventListeners() {
        this.prevMonthBtn.addEventListener('click', () => this.changeMonth(-1));
        this.nextMonthBtn.addEventListener('click', () => this.changeMonth(1));
    }

    changeMonth(delta) {
        this.currentMonth.setMonth(this.currentMonth.getMonth() + delta);
        this.render();
    }

    render() {
        // Update title
        const monthName = this.currentMonth.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
        this.calendarTitle.textContent = monthName.charAt(0).toUpperCase() + monthName.slice(1);

        // Calculate month boundaries
        const year = this.currentMonth.getFullYear();
        const month = this.currentMonth.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDayOfWeek = firstDay.getDay();

        // Get executions for the month
        const startTime = firstDay.getTime();
        const endTime = lastDay.getTime();
        const executions = this.schedulerUI.getExecutionsInRange(startTime, endTime);

        // Count executions per day
        const executionsPerDay = {};
        Object.values(executions).forEach(taskExecutions => {
            taskExecutions.forEach(timestamp => {
                const date = new Date(timestamp);
                const dayKey = `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`;
                executionsPerDay[dayKey] = (executionsPerDay[dayKey] || 0) + 1;
            });
        });

        // Render calendar days
        let html = '';

        // Empty cells before first day
        for (let i = 0; i < startingDayOfWeek; i++) {
            html += '<div class="calendar-day empty"></div>';
        }

        // Days of the month
        const currentSimulatedDay = this.schedulerUI.currentTime.toDateString();

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dayKey = `${year}-${month}-${day}`;
            const executionCount = executionsPerDay[dayKey] || 0;
            const isCurrentDay = date.toDateString() === currentSimulatedDay;

            const classes = ['calendar-day'];
            if (isCurrentDay) classes.push('current-day');
            if (executionCount > 0) classes.push('has-tasks');

            html += `
                <div class="${classes.join(' ')}" title="${executionCount} tâche(s)">
                    <span class="day-number">${day}</span>
                    ${executionCount > 0 ? `<span class="task-count">${executionCount}</span>` : ''}
                </div>
            `;
        }

        this.calendarDays.innerHTML = html;
    }
}

// Initialize calendar when scheduler is ready
// Wait for schedulerUI to be fully initialized
function initCalendar() {
    if (window.schedulerUI) {
        window.calendarUI = new CalendarUI(window.schedulerUI);
        console.log('Calendar initialized successfully');
    } else {
        // Retry after a short delay
        setTimeout(initCalendar, 50);
    }
}

// Start initialization when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCalendar);
} else {
    // DOM already loaded, start immediately
    initCalendar();
}
