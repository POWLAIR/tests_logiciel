// Scheduler UI Application
class SchedulerUI {
    constructor() {
        this.tasks = new Map();
        this.currentTime = new Date('2025-01-15T08:00:00');
        this.executionLog = [];
        this.totalExecutions = 0;

        this.initializeElements();
        this.attachEventListeners();
        this.updateDisplay();
    }

    initializeElements() {
        // Form elements
        this.taskNameInput = document.getElementById('task-name');
        this.taskPeriodicitySelect = document.getElementById('task-periodicity');
        this.addTaskBtn = document.getElementById('add-task-btn');
        this.taskTypeRadios = document.querySelectorAll('input[name="task-type"]');
        this.dateTimePicker = document.getElementById('date-time-picker');
        this.taskDateInput = document.getElementById('task-date');
        this.taskTimeInput = document.getElementById('task-time');

        // Display elements
        this.tasksList = document.getElementById('tasks-list');
        this.executionLogEl = document.getElementById('execution-log');
        this.totalTasksEl = document.getElementById('total-tasks');
        this.totalExecutionsEl = document.getElementById('total-executions');
        this.currentTimeEl = document.getElementById('current-time');
        this.simulationTimeEl = document.getElementById('simulation-time');
        this.simulationDateEl = document.getElementById('simulation-date');

        // Control buttons
        this.tickBtn = document.getElementById('tick-btn');
        this.advanceHourBtn = document.getElementById('advance-hour-btn');
        this.advanceDayBtn = document.getElementById('advance-day-btn');
        this.resetBtn = document.getElementById('reset-btn');
    }

    attachEventListeners() {
        this.addTaskBtn.addEventListener('click', () => this.addTask());
        this.taskNameInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.addTask();
        });

        // Task type radio buttons
        this.taskTypeRadios.forEach(radio => {
            radio.addEventListener('change', (e) => this.handleTaskTypeChange(e.target.value));
        });

        this.tickBtn.addEventListener('click', () => this.tick());
        this.advanceHourBtn.addEventListener('click', () => this.advanceTime(60));
        this.advanceDayBtn.addEventListener('click', () => this.advanceTime(24 * 60));
        this.resetBtn.addEventListener('click', () => this.reset());
    }

    handleTaskTypeChange(type) {
        if (type === 'one-time') {
            this.dateTimePicker.style.display = 'grid';
            this.taskPeriodicitySelect.style.display = 'none';
            // Set default date to tomorrow
            const tomorrow = new Date(this.currentTime);
            tomorrow.setDate(tomorrow.getDate() + 1);
            this.taskDateInput.value = tomorrow.toISOString().split('T')[0];
        } else {
            this.dateTimePicker.style.display = 'none';
            this.taskPeriodicitySelect.style.display = 'block';
        }
    }

    addTask() {
        const name = this.taskNameInput.value.trim();
        const taskType = document.querySelector('input[name="task-type"]:checked').value;
        let periodicity;
        const autoRemove = document.getElementById('auto-remove').checked;

        if (!name) {
            this.showNotification('Veuillez entrer un nom de tâche', 'error');
            return;
        }

        if (this.tasks.has(name)) {
            this.showNotification('Une tâche avec ce nom existe déjà', 'error');
            return;
        }

        // Generate periodicity based on task type
        if (taskType === 'one-time') {
            const date = this.taskDateInput.value;
            const time = this.taskTimeInput.value;
            if (!date || !time) {
                this.showNotification('Veuillez sélectionner une date et heure', 'error');
                return;
            }
            periodicity = `@${date} ${time}`;
        } else {
            periodicity = this.taskPeriodicitySelect.value;
        }

        const task = {
            name,
            periodicity,
            lastExecution: null,
            executionCount: 0,
            autoRemove: autoRemove || taskType === 'one-time' // One-time tasks auto-remove by default
        };

        this.tasks.set(name, task);
        this.taskNameInput.value = '';
        document.getElementById('auto-remove').checked = false;
        this.renderTasks();
        this.updateStats();
        this.showNotification(`Tâche "${name}" ajoutée !`, 'success');
    }

    removeTask(name) {
        this.tasks.delete(name);
        this.renderTasks();
        this.updateStats();
        this.showNotification(`Tâche "${name}" supprimée`, 'info');
    }

    tick() {
        this.currentTime = new Date(this.currentTime.getTime() + 60 * 1000);

        let executed = 0;
        const tasksToRemove = [];

        this.tasks.forEach((task, name) => {
            if (this.shouldExecute(task)) {
                this.executeTask(task);
                executed++;

                // Mark for removal if autoRemove is enabled
                if (task.autoRemove) {
                    tasksToRemove.push(name);
                }
            }
        });

        // Remove marked tasks
        tasksToRemove.forEach(name => {
            this.tasks.delete(name);
            this.showNotification(`Tâche "${name}" auto-supprimée après exécution`, 'info');
        });

        this.updateDisplay();
        this.renderTasks();

        if (executed > 0) {
            this.showNotification(`${executed} tâche(s) exécutée(s)`, 'success');
        }
    }

    shouldExecute(task) {
        const { periodicity, lastExecution } = task;
        const currentTimestamp = this.currentTime.getTime();

        // For '@YYYY-MM-DD HH:MM' (one-time task)
        const matchOneTime = periodicity.match(/^@(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2})$/);
        if (matchOneTime) {
            // If already executed, never execute again
            if (lastExecution !== null) {
                return false;
            }

            const targetDate = new Date(
                parseInt(matchOneTime[1]),
                parseInt(matchOneTime[2]) - 1,
                parseInt(matchOneTime[3]),
                parseInt(matchOneTime[4]),
                parseInt(matchOneTime[5])
            );

            const targetTimestamp = targetDate.getTime();
            const currentHour = this.currentTime.getHours();
            const currentMinute = this.currentTime.getMinutes();
            const currentDay = this.currentTime.toDateString();
            const targetDay = targetDate.toDateString();

            // Check if we're at the exact date and time
            return currentDay === targetDay &&
                currentHour === parseInt(matchOneTime[4]) &&
                currentMinute === parseInt(matchOneTime[5]);
        }

        // Pour '*' (chaque minute)
        if (periodicity === '*') {
            if (!lastExecution) return true;
            const elapsed = (currentTimestamp - lastExecution) / 1000;
            return elapsed >= 60;
        }

        // Pour '*/N' (toutes les N minutes)
        const matchMinutes = periodicity.match(/^\*\/(\d+)$/);
        if (matchMinutes) {
            if (!lastExecution) return true;
            const minutes = parseInt(matchMinutes[1]);
            const elapsed = (currentTimestamp - lastExecution) / 1000;
            return elapsed >= (minutes * 60);
        }

        // Pour '0 H * * *' (heures fixes)
        const matchHourly = periodicity.match(/^(\d+)\s+(\d+)\s+\*\s+\*\s+\*$/);
        if (matchHourly) {
            const targetMinute = parseInt(matchHourly[1]);
            const targetHour = parseInt(matchHourly[2]);
            const currentHour = this.currentTime.getHours();
            const currentMinute = this.currentTime.getMinutes();
            const currentDay = this.currentTime.toDateString();

            if (currentHour !== targetHour || currentMinute !== targetMinute) {
                return false;
            }

            if (!lastExecution) return true;

            const lastDay = new Date(lastExecution).toDateString();
            return currentDay !== lastDay;
        }

        // Pour '0 H * * D' (jours de la semaine)
        const matchWeekly = periodicity.match(/^(\d+)\s+(\d+)\s+\*\s+\*\s+(\d+)$/);
        if (matchWeekly) {
            const targetMinute = parseInt(matchWeekly[1]);
            const targetHour = parseInt(matchWeekly[2]);
            const targetDay = parseInt(matchWeekly[3]);
            const currentHour = this.currentTime.getHours();
            const currentMinute = this.currentTime.getMinutes();
            const currentDay = this.currentTime.getDay();
            const currentDayStr = this.currentTime.toDateString();

            if (currentDay !== targetDay) return false;
            if (currentHour !== targetHour || currentMinute !== targetMinute) return false;

            if (!lastExecution) return true;

            const lastDayStr = new Date(lastExecution).toDateString();
            return currentDayStr !== lastDayStr;
        }

        // Pour '0 H D * *' (jour du mois)
        const matchMonthly = periodicity.match(/^(\d+)\s+(\d+)\s+(\d+)\s+\*\s+\*$/);
        if (matchMonthly) {
            const targetMinute = parseInt(matchMonthly[1]);
            const targetHour = parseInt(matchMonthly[2]);
            const targetDayOfMonth = parseInt(matchMonthly[3]);
            const currentHour = this.currentTime.getHours();
            const currentMinute = this.currentTime.getMinutes();
            const currentDayOfMonth = this.currentTime.getDate();
            const currentDayStr = this.currentTime.toDateString();

            if (currentDayOfMonth !== targetDayOfMonth) return false;
            if (currentHour !== targetHour || currentMinute !== targetMinute) return false;

            if (!lastExecution) return true;

            const lastDayStr = new Date(lastExecution).toDateString();
            return currentDayStr !== lastDayStr;
        }

        return false;
    }

    executeTask(task) {
        task.lastExecution = this.currentTime.getTime();
        task.executionCount++;
        this.totalExecutions++;

        const logEntry = {
            time: this.formatTime(this.currentTime),
            task: task.name,
            message: `Exécutée (${task.executionCount}x)`
        };

        this.executionLog.unshift(logEntry);
        if (this.executionLog.length > 50) {
            this.executionLog.pop();
        }

        this.renderLog();
    }

    advanceTime(minutes) {
        const ticksNeeded = minutes;

        for (let i = 0; i < ticksNeeded; i++) {
            this.tick();
        }

        this.showNotification(`Avancé de ${minutes} minute(s)`, 'info');
    }

    reset() {
        this.tasks.clear();
        this.currentTime = new Date('2025-01-15T08:00:00');
        this.executionLog = [];
        this.totalExecutions = 0;

        this.renderTasks();
        this.renderLog();
        this.updateDisplay();
        this.showNotification('Scheduler réinitialisé', 'info');
    }

    renderTasks() {
        if (this.tasks.size === 0) {
            this.tasksList.innerHTML = '<div class="log-empty">Aucune tâche planifiée</div>';
            return;
        }

        this.tasksList.innerHTML = Array.from(this.tasks.values()).map(task => `
            <div class="task-item">
                <div class="task-header">
                    <span class="task-name">${task.name}</span>
                    <div class="task-actions">
                        <button class="task-btn" onclick="schedulerUI.removeTask('${task.name}')" title="Supprimer">
                            🗑️
                        </button>
                    </div>
                </div>
                <div class="task-info">
                    <span class="task-badge">${this.getPeriodicityLabel(task.periodicity)}</span>
                    ${task.autoRemove ? '<span class="task-badge badge-auto-remove">🔄 Auto-suppression</span>' : ''}
                    <span>${task.executionCount} exécution(s)</span>
                </div>
            </div>
        `).join('');
    }

    renderLog() {
        if (this.executionLog.length === 0) {
            this.executionLogEl.innerHTML = '<div class="log-empty">Aucune exécution pour le moment...</div>';
            return;
        }

        this.executionLogEl.innerHTML = this.executionLog.map(entry => `
            <div class="log-entry">
                <span class="log-time">${entry.time}</span>
                <span class="log-task">${entry.task}</span>
                <span class="log-message">${entry.message}</span>
            </div>
        `).join('');
    }

    updateDisplay() {
        this.simulationTimeEl.textContent = this.formatTime(this.currentTime);
        this.simulationDateEl.textContent = this.formatDate(this.currentTime);
        this.currentTimeEl.textContent = this.formatTime(this.currentTime);
        this.updateStats();
    }

    updateStats() {
        this.totalTasksEl.textContent = this.tasks.size;
        this.totalExecutionsEl.textContent = this.totalExecutions;
    }

    formatTime(date) {
        return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    formatDate(date) {
        return date.toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            weekday: 'long'
        });
    }

    getPeriodicityLabel(periodicity) {
        // Check for one-time task format
        const matchOneTime = periodicity.match(/^@(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2})$/);
        if (matchOneTime) {
            const date = new Date(
                parseInt(matchOneTime[1]),
                parseInt(matchOneTime[2]) - 1,
                parseInt(matchOneTime[3]),
                parseInt(matchOneTime[4]),
                parseInt(matchOneTime[5])
            );
            return `📅 ${date.toLocaleDateString('fr-FR')} à ${matchOneTime[4]}:${matchOneTime[5]}`;
        }

        const labels = {
            '*': 'Chaque minute',
            '*/2': 'Toutes les 2 min',
            '*/5': 'Toutes les 5 min',
            '*/10': 'Toutes les 10 min',
            '0 9 * * *': '9h tous les jours',
            '0 14 * * *': '14h tous les jours',
            '0 9 * * 1': 'Lundi 9h',
            '0 9 * * 5': 'Vendredi 9h',
            '0 9 1 * *': '1er du mois à 9h',
            '0 9 15 * *': '15 du mois à 9h'
        };
        return labels[periodicity] || periodicity;
    }

    showNotification(message, type = 'info') {
        // Simple console log for now - could be enhanced with toast notifications
        const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
        console.log(`${icon} ${message}`);
    }
}

// Initialize the application
let schedulerUI;
document.addEventListener('DOMContentLoaded', () => {
    schedulerUI = new SchedulerUI();
});
