// Scheduler UI Application
class SchedulerUI {
    constructor() {
        this.tasks = new Map();
        this.currentTime = new Date('2025-01-15T08:00:00');
        this.executionLog = [];
        this.totalExecutions = 0;
        this.editMode = false;
        this.editingTaskName = null;
        this.toasts = [];

        this.initializeElements();
        this.attachEventListeners();
        this.createToastContainer();
        this.updateDisplay();
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    initializeElements() {
        // Form elements
        this.taskNameInput = document.getElementById('task-name');
        this.taskPeriodicitySelect = document.getElementById('task-periodicity');
        this.customPeriodicityInput = document.getElementById('custom-periodicity');
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

        // Periodicity select - detect custom
        this.taskPeriodicitySelect.addEventListener('change', (e) => {
            if (e.target.value === 'custom') {
                this.customPeriodicityInput.style.display = 'block';
                this.customPeriodicityInput.focus();
            } else {
                this.customPeriodicityInput.style.display = 'none';
            }
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

        // If in edit mode, call updateTask instead
        if (this.editMode) {
            this.updateTask(this.editingTaskName);
            return;
        }

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
            const selectedValue = this.taskPeriodicitySelect.value;
            if (selectedValue === 'custom') {
                periodicity = this.customPeriodicityInput.value.trim();
                if (!periodicity) {
                    this.showNotification('Veuillez entrer une périodicité personnalisée', 'error');
                    return;
                }
                // Basic validation
                if (!this.validatePeriodicityFormat(periodicity)) {
                    this.showNotification('Format de périodicité invalide', 'error');
                    return;
                }
            } else {
                periodicity = selectedValue;
            }
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

        // Reset custom periodicity if used
        if (this.taskPeriodicitySelect.value === 'custom') {
            this.customPeriodicityInput.value = '';
            this.customPeriodicityInput.style.display = 'none';
            this.taskPeriodicitySelect.value = '*';
        }

        this.renderTasks();
        this.updateStats();
        this.updateCalendar();
        this.showNotification(`Tâche "${name}" ajoutée !`, 'success');
    }

    editTask(name) {
        const task = this.tasks.get(name);
        if (!task) return;

        this.editMode = true;
        this.editingTaskName = name;

        // Pre-fill form
        this.taskNameInput.value = name;
        this.taskNameInput.disabled = true; // Can't change name while editing

        // Check if one-time task
        if (task.periodicity.startsWith('@')) {
            document.querySelector('input[name="task-type"][value="one-time"]').checked = true;
            this.handleTaskTypeChange('one-time');

            const match = task.periodicity.match(/^@(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2})$/);
            if (match) {
                this.taskDateInput.value = match[1];
                this.taskTimeInput.value = match[2];
            }
        } else {
            document.querySelector('input[name="task-type"][value="recurring"]').checked = true;
            this.handleTaskTypeChange('recurring');

            // Check if it's a predefined value
            const option = Array.from(this.taskPeriodicitySelect.options)
                .find(opt => opt.value === task.periodicity && opt.value !== 'custom');

            if (option) {
                this.taskPeriodicitySelect.value = task.periodicity;
            } else {
                // Custom periodicity
                this.taskPeriodicitySelect.value = 'custom';
                this.customPeriodicityInput.value = task.periodicity;
                this.customPeriodicityInput.style.display = 'block';
            }
        }

        document.getElementById('auto-remove').checked = task.autoRemove;

        // Change button text
        this.addTaskBtn.innerHTML = '<span>💾</span> Mettre à jour';
        this.addTaskBtn.classList.add('btn-update');

        // Add cancel button
        if (!document.getElementById('cancel-edit-btn')) {
            const cancelBtn = document.createElement('button');
            cancelBtn.id = 'cancel-edit-btn';
            cancelBtn.className = 'btn btn-secondary';
            cancelBtn.innerHTML = '<span>❌</span> Annuler';
            cancelBtn.onclick = () => this.cancelEdit();
            this.addTaskBtn.parentElement.appendChild(cancelBtn);
        }

        this.showNotification('Mode édition activé', 'info');
    }

    cancelEdit() {
        this.editMode = false;
        this.editingTaskName = null;

        this.taskNameInput.value = '';
        this.taskNameInput.disabled = false;
        document.getElementById('auto-remove').checked = false;
        this.customPeriodicityInput.value = '';
        this.customPeriodicityInput.style.display = 'none';
        this.taskPeriodicitySelect.value = '*';

        this.addTaskBtn.innerHTML = '<span>➕</span> Ajouter';
        this.addTaskBtn.classList.remove('btn-update');

        const cancelBtn = document.getElementById('cancel-edit-btn');
        if (cancelBtn) cancelBtn.remove();

        this.showNotification('Édition annulée', 'info');
    }

    updateTask(name) {
        const task = this.tasks.get(name);
        if (!task) return;

        const taskType = document.querySelector('input[name="task-type"]:checked').value;
        let periodicity;
        const autoRemove = document.getElementById('auto-remove').checked;

        // Generate new periodicity
        if (taskType === 'one-time') {
            const date = this.taskDateInput.value;
            const time = this.taskTimeInput.value;
            if (!date || !time) {
                this.showNotification('Veuillez sélectionner une date et heure', 'error');
                return;
            }
            periodicity = `@${date} ${time}`;
        } else {
            const selectedValue = this.taskPeriodicitySelect.value;
            if (selectedValue === 'custom') {
                periodicity = this.customPeriodicityInput.value.trim();
                if (!periodicity || !this.validatePeriodicityFormat(periodicity)) {
                    this.showNotification('Format de périodicité invalide', 'error');
                    return;
                }
            } else {
                periodicity = selectedValue;
            }
        }

        // Update task (keep lastExecution)
        task.periodicity = periodicity;
        task.autoRemove = autoRemove || taskType === 'one-time';

        this.tasks.set(name, task);
        this.cancelEdit();
        this.renderTasks();
        this.showNotification(`Tâche "${name}" mise à jour !`, 'success');
    }

    removeTask(name) {
        this.tasks.delete(name);
        this.renderTasks();
        this.updateStats();
        this.updateCalendar();
        this.showNotification(`Tâche "${name}" supprimée`, 'info');
    }

    updateCalendar() {
        if (window.calendarUI) {
            window.calendarUI.render();
        }
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
                        <button class="task-btn" onclick="schedulerUI.editTask('${task.name}')" title="Éditer">
                            ✏️
                        </button>
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
                <div class="task-next-exec">
                    ${this.getNextExecutionDisplay(task)}
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

    validatePeriodicityFormat(periodicity) {
        // Validate common periodicity formats
        const patterns = [
            /^\*$/,                                    // *
            /^\*\/\d+$/,                               // */N
            /^\d+\s+\d+\s+\*\s+\*\s+\*$/,             // M H * * *
            /^\d+\s+\d+\s+\*\s+\*\s+\d+$/,            // M H * * D (day of week)
            /^\d+\s+\d+\s+\d+\s+\*\s+\*$/             // M H D * * (day of month)
        ];

        return patterns.some(pattern => pattern.test(periodicity));
    }

    getNextExecution(task) {
        const { periodicity, lastExecution } = task;
        const currentTime = this.currentTime.getTime();

        // For one-time tasks
        const matchOneTime = periodicity.match(/^@(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2})$/);
        if (matchOneTime) {
            if (lastExecution !== null) return null; // Already executed

            const targetDate = new Date(
                parseInt(matchOneTime[1]),
                parseInt(matchOneTime[2]) - 1,
                parseInt(matchOneTime[3]),
                parseInt(matchOneTime[4]),
                parseInt(matchOneTime[5])
            );
            return targetDate.getTime();
        }

        // For */N (every N minutes)
        if (periodicity.match(/^\*\/(\d+)$/)) {
            const minutes = parseInt(periodicity.match(/^\*\/(\d+)$/)[1]);
            if (!lastExecution) {
                return currentTime + (minutes * 60 * 1000);
            }
            return lastExecution + (minutes * 60 * 1000);
        }

        // For * (every minute)
        if (periodicity === '*') {
            if (!lastExecution) return currentTime + 60000;
            return lastExecution + 60000;
        }

        // For M H * * * (daily at specific time)
        const matchDaily = periodicity.match(/^(\d+)\s+(\d+)\s+\*\s+\*\s+\*$/);
        if (matchDaily) {
            const targetMinute = parseInt(matchDaily[1]);
            const targetHour = parseInt(matchDaily[2]);
            const now = new Date(currentTime);
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate(), targetHour, targetMinute, 0);

            if (today.getTime() > currentTime) {
                return today.getTime();
            }
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            return tomorrow.getTime();
        }

        // For M H * * D (specific day of week)
        const matchWeekly = periodicity.match(/^(\d+)\s+(\d+)\s+\*\s+\*\s+(\d+)$/);
        if (matchWeekly) {
            const targetMinute = parseInt(matchWeekly[1]);
            const targetHour = parseInt(matchWeekly[2]);
            const targetDay = parseInt(matchWeekly[3]);

            let searchDate = new Date(currentTime);
            for (let i = 0; i < 8; i++) {
                if (searchDate.getDay() === targetDay) {
                    const candidate = new Date(searchDate.getFullYear(), searchDate.getMonth(), searchDate.getDate(), targetHour, targetMinute, 0);
                    if (candidate.getTime() > currentTime) {
                        return candidate.getTime();
                    }
                }
                searchDate.setDate(searchDate.getDate() + 1);
            }
        }

        // For M H D * * (specific day of month)
        const matchMonthly = periodicity.match(/^(\d+)\s+(\d+)\s+(\d+)\s+\*\s+\*$/);
        if (matchMonthly) {
            const targetMinute = parseInt(matchMonthly[1]);
            const targetHour = parseInt(matchMonthly[2]);
            const targetDay = parseInt(matchMonthly[3]);
            const now = new Date(currentTime);
            const thisMonth = new Date(now.getFullYear(), now.getMonth(), targetDay, targetHour, targetMinute, 0);

            if (thisMonth.getTime() > currentTime) {
                return thisMonth.getTime();
            }
            const nextMonth = new Date(thisMonth);
            nextMonth.setMonth(nextMonth.getMonth() + 1);
            return nextMonth.getTime();
        }

        return null;
    }

    getNextExecutionDisplay(task) {
        const nextTime = this.getNextExecution(task);

        if (nextTime === null) {
            if (task.lastExecution !== null) {
                return '<span class="next-exec executed">✅ Déjà exécutée</span>';
            }
            return '<span class="next-exec">⏱️ N/A</span>';
        }

        const nextDate = new Date(nextTime);
        const diffMs = nextTime - this.currentTime.getTime();
        const diffMinutes = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMinutes / 60);
        const diffDays = Math.floor(diffHours / 24);

        let countdown = '';
        if (diffDays > 0) {
            countdown = `Dans ${diffDays}j ${diffHours % 24}h`;
        } else if (diffHours > 0) {
            countdown = `Dans ${diffHours}h ${diffMinutes % 60}min`;
        } else if (diffMinutes > 0) {
            countdown = `Dans ${diffMinutes}min`;
        } else {
            countdown = 'Imminent';
        }

        const dateStr = nextDate.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const timeStr = nextDate.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });

        return `<span class="next-exec">⏱️ Prochaine : ${dateStr} ${timeStr} <em>(${countdown})</em></span>`;
    }

    getExecutionsInRange(startTime, endTime) {
        const result = {};

        this.tasks.forEach((task, name) => {
            const executions = [];
            const { periodicity } = task;

            // For one-time tasks
            const matchOneTime = periodicity.match(/^@(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2})$/);
            if (matchOneTime) {
                const targetDate = new Date(
                    parseInt(matchOneTime[1]),
                    parseInt(matchOneTime[2]) - 1,
                    parseInt(matchOneTime[3]),
                    parseInt(matchOneTime[4]),
                    parseInt(matchOneTime[5])
                );
                const targetTimestamp = targetDate.getTime();

                if (targetTimestamp >= startTime && targetTimestamp <= endTime) {
                    executions.push(targetTimestamp);
                }
            } else {
                // For recurring tasks, iterate through each day
                let currentDay = new Date(startTime);
                currentDay.setHours(0, 0, 0, 0);

                while (currentDay.getTime() <= endTime) {
                    // Test each hour and minute of the day
                    for (let hour = 0; hour < 24; hour++) {
                        for (let minute = 0; minute < 60; minute++) {
                            const testTime = new Date(currentDay);
                            testTime.setHours(hour, minute, 0, 0);
                            const testTimestamp = testTime.getTime();

                            if (testTimestamp >= startTime && testTimestamp <= endTime) {
                                const mockTask = {
                                    periodicity: periodicity,
                                    lastExecution: null
                                };

                                if (this.shouldExecute(mockTask, testTimestamp)) {
                                    executions.push(testTimestamp);
                                    break; // Only one execution per hour/minute combination
                                }
                            }
                        }
                        if (executions.length > 0 && executions[executions.length - 1] >= currentDay.getTime() &&
                            executions[executions.length - 1] < currentDay.getTime() + 86400000) {
                            break; // Found execution for this day
                        }
                    }

                    currentDay.setDate(currentDay.getDate() + 1);
                }
            }

            if (executions.length > 0) {
                result[name] = executions;
            }
        });

        return result;
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
        const icons = {
            success: '✅',
            error: '❌',
            info: 'ℹ️',
            warning: '⚠️'
        };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <span class="toast-message">${message}</span>
        `;

        const container = document.getElementById('toast-container');
        container.appendChild(toast);

        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Initialize the application
let schedulerUI;
document.addEventListener('DOMContentLoaded', () => {
    schedulerUI = new SchedulerUI();
});
