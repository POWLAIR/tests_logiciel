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
        console.log('Calendar render() called');
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

        console.log(`Rendering calendar for ${monthName}: ${daysInMonth} days, starting on day ${startingDayOfWeek}`);

        // Get executions for the month
        const startTime = firstDay.getTime();
        const endTime = lastDay.getTime();
        const executions = this.schedulerUI.getExecutionsInRange(startTime, endTime);
        console.log('Executions in range:', executions);

        // Group tasks by day with details
        const tasksPerDay = {};
        Object.entries(executions).forEach(([taskName, timestamps]) => {
            timestamps.forEach(timestamp => {
                const date = new Date(timestamp);
                const dayKey = `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`;

                if (!tasksPerDay[dayKey]) tasksPerDay[dayKey] = [];
                tasksPerDay[dayKey].push({
                    name: taskName,
                    time: date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
                    timestamp: timestamp
                });
            });
        });

        // Sort tasks by time within each day
        Object.keys(tasksPerDay).forEach(dayKey => {
            tasksPerDay[dayKey].sort((a, b) => a.timestamp - b.timestamp);
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
            const tasksForDay = tasksPerDay[dayKey] || [];
            const isCurrentDay = date.toDateString() === currentSimulatedDay;

            const classes = ['calendar-day'];
            if (isCurrentDay) classes.push('current-day');
            if (tasksForDay.length > 0) classes.push('has-tasks');

            html += `
                <div class="${classes.join(' ')}" title="${tasksForDay.length} tâche(s)">
                    <span class="day-number">${day}</span>
                    <div class="day-tasks">
                        ${tasksForDay.slice(0, 3).map(t => `
                            <div class="task-mini">
                                <span class="task-dot"></span>
                                <span class="task-time">${t.time}</span>
                                <span class="task-name">${t.name}</span>
                            </div>
                        `).join('')}
                        ${tasksForDay.length > 3 ? `<div class="task-more">+${tasksForDay.length - 3} autre${tasksForDay.length > 4 ? 's' : ''}</div>` : ''}
                    </div>
                </div>
            `;
        }


        console.log('Generated HTML length:', html.length);
        console.log('Setting innerHTML on calendar-days:', this.calendarDays);
        this.calendarDays.innerHTML = html;
        console.log('Calendar rendered successfully');
    }
}

// Initialize calendar when scheduler is ready
// Wait for schedulerUI to be fully initialized
function initCalendar() {
    console.log('initCalendar() called, checking for schedulerUI...');
    if (window.schedulerUI) {
        console.log('schedulerUI found, creating CalendarUI...');
        window.calendarUI = new CalendarUI(window.schedulerUI);
        console.log('Calendar initialized successfully');
    } else {
        console.log('schedulerUI not ready yet, retrying in 50ms...');
        // Retry after a short delay
        setTimeout(initCalendar, 50);
    }
}

// Start initialization when DOM is ready
if (document.readyState === 'loading') {
    console.log('DOM still loading, adding event listener...');
    document.addEventListener('DOMContentLoaded', initCalendar);
} else {
    console.log('DOM already loaded, starting init immediately...');
    // DOM already loaded, start immediately
    initCalendar();
}
