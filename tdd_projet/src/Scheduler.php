<?php

namespace Scheduler;

/**
 * Gestionnaire de tâches planifiées
 */
class Scheduler
{
    /**
     * Liste des tâches planifiées
     * @var array
     */
    private array $tasks = [];

    /**
     * Fournisseur de temps
     * @var TimeProviderInterface
     */
    private TimeProviderInterface $timeProvider;

    /**
     * Constructeur
     * 
     * @param TimeProviderInterface|null $timeProvider Fournisseur de temps (optionnel)
     */
    public function __construct(?TimeProviderInterface $timeProvider = null)
    {
        $this->timeProvider = $timeProvider ?? new SystemTimeProvider();
    }

    /**
     * Retourne la liste des tâches planifiées
     * 
     * @return array
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * Planifie une tâche
     * 
     * @param string $name Nom de la tâche
     * @param callable $callback Fonction à exécuter
     * @param string $periodicity Périodicité ('*' = chaque minute)
     * @param bool $autoRemove Supprimer automatiquement après exécution (pour one-time)
     * @return void
     * @throws \InvalidArgumentException Si une tâche avec ce nom existe déjà
     */
    public function scheduleTask(string $name, callable $callback, string $periodicity = '*', bool $autoRemove = false): void
    {
        if (isset($this->tasks[$name])) {
            throw new \InvalidArgumentException("Task with name '{$name}' already exists.");
        }

        $this->tasks[$name] = [
            'callback' => $callback,
            'periodicity' => $periodicity,
            'lastExecution' => null,
            'autoRemove' => $autoRemove
        ];
    }

    /**
     * Met à jour une tâche existante
     * 
     * @param string $name Nom de la tâche à mettre à jour
     * @param callable $callback Nouvelle fonction à exécuter
     * @param string $periodicity Nouvelle périodicité
     * @param bool $autoRemove Supprimer automatiquement après exécution
     * @return void
     * @throws \InvalidArgumentException Si la tâche n'existe pas
     */
    public function updateTask(string $name, callable $callback, string $periodicity = '*', bool $autoRemove = false): void
    {
        if (!isset($this->tasks[$name])) {
            throw new \InvalidArgumentException("Task with name '{$name}' does not exist.");
        }

        // Garder la dernière exécution pour ne pas réinitialiser
        $lastExecution = $this->tasks[$name]['lastExecution'];

        $this->tasks[$name] = [
            'callback' => $callback,
            'periodicity' => $periodicity,
            'lastExecution' => $lastExecution,
            'autoRemove' => $autoRemove
        ];
    }

    /**
     * Supprime une tâche planifiée
     * 
     * @param string $name Nom de la tâche à supprimer
     * @return void
     */
    public function removeTask(string $name): void
    {
        unset($this->tasks[$name]);
    }

    /**
     * Exécute les tâches dues au moment actuel
     * 
     * @return void
     */
    public function tick(): void
    {
        $currentTime = $this->timeProvider->getCurrentTime();
        $tasksToRemove = [];
        
        foreach ($this->tasks as $name => &$task) {
            if ($this->shouldExecute($task, $currentTime)) {
                $task['callback']();
                $task['lastExecution'] = $currentTime;
                
                // Marquer pour suppression si auto_remove activé
                if ($task['autoRemove'] ?? false) {
                    $tasksToRemove[] = $name;
                }
            }
        }
        
        // Supprimer les tâches marquées
        foreach ($tasksToRemove as $name) {
            unset($this->tasks[$name]);
        }
    }

    /**
     * Récupère le prochain timestamp d'exécution d'une tâche
     * 
     * @param string $name Nom de la tâche
     * @return int|null Timestamp de la prochaine exécution, ou null si tâche inexistante ou déjà exécutée (one-time)
     */
    public function getNextExecution(string $name): ?int
    {
        if (!isset($this->tasks[$name])) {
            return null;
        }

        $task = $this->tasks[$name];
        $periodicity = $task['periodicity'];
        $currentTime = $this->timeProvider->getCurrentTime();
        $lastExecution = $task['lastExecution'];

        // Pour tâches one-time (@YYYY-MM-DD HH:MM)
        if (preg_match('/^@(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2})$/', $periodicity, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            $day = (int)$matches[3];
            $hour = (int)$matches[4];
            $minute = (int)$matches[5];
            
            $targetTimestamp = mktime($hour, $minute, 0, $month, $day, $year);
            
            // Si déjà exécutée, pas de prochaine exécution
            if ($lastExecution !== null) {
                return null;
            }
            
            return $targetTimestamp;
        }

        // Pour '*/N' (toutes les N minutes)
        if (preg_match('/^\*\/(\d+)$/', $periodicity, $matches)) {
            $minutes = (int)$matches[1];
            
            if ($lastExecution === null) {
                // Première exécution : dans N minutes
                return $currentTime + ($minutes * 60);
            }
            
            // Prochaine exécution : lastExecution + N minutes
            return $lastExecution + ($minutes * 60);
        }

        // Pour '*' (chaque minute)
        if ($periodicity === '*') {
            if ($lastExecution === null) {
                return $currentTime + 60;
            }
            return $lastExecution + 60;
        }

        // Pour '0 H * * *' (heures fixes quotidiennes)
        if (preg_match('/^(\d+)\s+(\d+)\s+\*\s+\*\s+\*$/', $periodicity, $matches)) {
            $targetMinute = (int)$matches[1];
            $targetHour = (int)$matches[2];
            
            $currentDay = date('Y-m-d', $currentTime);
            $targetToday = strtotime("$currentDay $targetHour:$targetMinute:00");
            
            // Si l'heure cible aujourd'hui est passée, c'est demain
            if ($currentTime >= $targetToday) {
                return strtotime("+1 day", $targetToday);
            }
            
            return $targetToday;
        }

        // Pour '0 H * * D' (jour de la semaine)
        if (preg_match('/^(\d+)\s+(\d+)\s+\*\s+\*\s+(\d+)$/', $periodicity, $matches)) {
            $targetMinute = (int)$matches[1];
            $targetHour = (int)$matches[2];
            $targetDayOfWeek = (int)$matches[3];
            
            // Trouver le prochain jour correspondant
            $current = $currentTime;
            for ($i = 0; $i < 8; $i++) { // Max 7 jours à chercher
                $dayOfWeek = (int)date('w', $current);
                $dayDate = date('Y-m-d', $current);
                $targetTime = strtotime("$dayDate $targetHour:$targetMinute:00");
                
                if ($dayOfWeek === $targetDayOfWeek && $targetTime > $currentTime) {
                    return $targetTime;
                }
                
                $current = strtotime("+1 day", $current);
            }
        }

        // Pour '0 H D * *' (jour du mois)
        if (preg_match('/^(\d+)\s+(\d+)\s+(\d+)\s+\*\s+\*$/', $periodicity, $matches)) {
            $targetMinute = (int)$matches[1];
            $targetHour = (int)$matches[2];
            $targetDayOfMonth = (int)$matches[3];
            
            $currentMonth = date('Y-m', $currentTime);
            $targetThisMonth = strtotime("$currentMonth-$targetDayOfMonth $targetHour:$targetMinute:00");
            
            // Si le jour cible ce mois est passé, c'est le mois prochain
            if ($targetThisMonth !== false && $currentTime >= $targetThisMonth) {
                $nextMonth = date('Y-m', strtotime("+1 month", $currentTime));
                return strtotime("$nextMonth-$targetDayOfMonth $targetHour:$targetMinute:00");
            }
            
            return $targetThisMonth;
        }

        return null;
    }

    /**
     * Récupère toutes les exécutions de toutes les tâches dans une plage de dates
     * ESSENTIEL pour afficher le calendrier !
     * 
     * @param int $startTime Timestamp de début
     * @param int $endTime Timestamp de fin
     * @return array Tableau associatif [taskName => [timestamps]]
     */
    public function getExecutionsInRange(int $startTime, int $endTime): array
    {
        $result = [];
        
        foreach ($this->tasks as $name => $task) {
            $executions = [];
            $periodicity = $task['periodicity'];
            
            // Pour tâches one-time
            if (preg_match('/^@(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2})$/', $periodicity, $matches)) {
                $year = (int)$matches[1];
                $month = (int)$matches[2];
                $day = (int)$matches[3];
                $hour = (int)$matches[4];
                $minute = (int)$matches[5];
                
                $targetTimestamp = mktime($hour, $minute, 0, $month, $day, $year);
                
                if ($targetTimestamp >= $startTime && $targetTimestamp <= $endTime) {
                    $executions[] = $targetTimestamp;
                }
            }
            // Pour tâches récurrentes, parcourir la période
            else {
                // Calculer chaque jour dans la plage
                $currentDay = $startTime;
                
                while ($currentDay <= $endTime) {
                    // Pour chaque jour, tester si la tâche doit s'exécuter
                    $dayStart = strtotime(date('Y-m-d 00:00:00', $currentDay));
                    $dayEnd = strtotime(date('Y-m-d 23:59:59', $currentDay));
                    
                    // Simuler l'exécution pour ce jour
                    $mockTask = [
                        'periodicity' => $periodicity,
                        'lastExecution' => null
                    ];
                    
                    // Tester chaque heure du jour
                    for ($hour = 0; $hour < 24; $hour++) {
                        for ($minute = 0; $minute < 60; $minute += 1) { // Tester chaque minute
                            $testTime = mktime($hour, $minute, 0, (int)date('m', $dayStart), (int)date('d', $dayStart), (int)date('Y', $dayStart));
                            
                            if ($testTime >= $startTime && $testTime <= $endTime) {
                                if ($this->shouldExecute($mockTask, $testTime)) {
                                    $executions[] = $testTime;
                                    // Pour éviter les doublons du même moment
                                    break 2; // Sort des boucles hour/minute
                                }
                            }
                        }
                    }
                    
                    $currentDay = strtotime('+1 day', $dayStart);
                }
            }
            
            if (!empty($executions)) {
                $result[$name] = $executions;
            }
        }
        
        return $result;
    }

    /**
     * Détermine si une tâche doit être exécutée
     * 
     * @param array $task Données de la tâche
     * @param int $currentTime Timestamp actuel
     * @return bool
     */
    private function shouldExecute(array $task, int $currentTime): bool
    {
        $periodicity = $task['periodicity'];
        $lastExecution = $task['lastExecution'];

        // Pour '*' (chaque minute) : vérifier si 60 secondes sont passées
        if ($periodicity === '*') {
            if ($lastExecution === null) {
                return true;
            }
            $elapsed = $currentTime - $lastExecution;
            return $elapsed >= 60;
        }

        // Pour '*/N' (toutes les N minutes) : vérifier si N*60 secondes sont passées
        if (preg_match('/^\*\/(\d+)$/', $periodicity, $matches)) {
            if ($lastExecution === null) {
                return true;
            }
            $minutes = (int)$matches[1];
            $elapsed = $currentTime - $lastExecution;
            return $elapsed >= ($minutes * 60);
        }

        // Pour '0 H * * *' (à une heure fixe tous les jours)
        // Format: minute heure jour mois jour_semaine
        if (preg_match('/^(\d+)\s+(\d+)\s+\*\s+\*\s+\*$/', $periodicity, $matches)) {
            $targetMinute = (int)$matches[1];
            $targetHour = (int)$matches[2];
            
            $currentHour = (int)date('H', $currentTime);
            $currentMinute = (int)date('i', $currentTime);
            $currentDay = date('Y-m-d', $currentTime);
            
            // Vérifier si on est à la bonne heure/minute
            if ($currentHour !== $targetHour || $currentMinute !== $targetMinute) {
                return false;
            }
            
            // Si jamais exécuté, exécuter
            if ($lastExecution === null) {
                return true;
            }
            
            // Vérifier qu'on n'a pas déjà exécuté aujourd'hui
            $lastExecutionDay = date('Y-m-d', $lastExecution);
            return $currentDay !== $lastExecutionDay;
        }

        // Pour '@YYYY-MM-DD HH:MM' (tâche one-time à date/heure spécifique)
        if (preg_match('/^@(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2})$/', $periodicity, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            $day = (int)$matches[3];
            $hour = (int)$matches[4];
            $minute = (int)$matches[5];
            
            // Créer le timestamp cible
            $targetTimestamp = mktime($hour, $minute, 0, $month, $day, $year);
            
            // Si déjà exécuté, ne JAMAIS réexécuter
            if ($lastExecution !== null) {
                return false;
            }
            
            // Vérifier si on est à l'heure exacte
            // Tolérance de 60 secondes pour catch le bon moment
            $currentHour = (int)date('H', $currentTime);
            $currentMinute = (int)date('i', $currentTime);
            $currentDay = date('Y-m-d', $currentTime);
            $targetDay = date('Y-m-d', $targetTimestamp);
            
            if ($currentDay !== $targetDay) {
                return false;
            }
            
            if ($currentHour !== $hour || $currentMinute !== $minute) {
                return false;
            }
            
            return true;
        }

        // Pour '0 H * * D' (à une heure fixe un jour de la semaine spécifique)
        // Format: minute heure jour mois jour_semaine
        // 0=Dimanche, 1=Lundi, 2=Mardi, 3=Mercredi, 4=Jeudi, 5=Vendredi, 6=Samedi
        if (preg_match('/^(\d+)\s+(\d+)\s+\*\s+\*\s+(\d+)$/', $periodicity, $matches)) {
            $targetMinute = (int)$matches[1];
            $targetHour = (int)$matches[2];
            $targetDayOfWeek = (int)$matches[3];
            
            $currentHour = (int)date('H', $currentTime);
            $currentMinute = (int)date('i', $currentTime);
            $currentDayOfWeek = (int)date('w', $currentTime); // 0=Sunday, 1=Monday, ...
            $currentDay = date('Y-m-d', $currentTime);
            
            // Vérifier si on est le bon jour de la semaine
            if ($currentDayOfWeek !== $targetDayOfWeek) {
                return false;
            }
            
            // Vérifier si on est à la bonne heure/minute
            if ($currentHour !== $targetHour || $currentMinute !== $targetMinute) {
                return false;
            }
            
            // Si jamais exécuté, exécuter
            if ($lastExecution === null) {
                return true;
            }
            
            // Vérifier qu'on n'a pas déjà exécuté aujourd'hui
            $lastExecutionDay = date('Y-m-d', $lastExecution);
            return $currentDay !== $lastExecutionDay;
        }

        // Pour '0 H D * *' (à une heure fixe un jour du mois spécifique)
        // Format: minute heure jour mois jour_semaine
        // D = 1-31 (jour du mois)
        if (preg_match('/^(\d+)\s+(\d+)\s+(\d+)\s+\*\s+\*$/', $periodicity, $matches)) {
            $targetMinute = (int)$matches[1];
            $targetHour = (int)$matches[2];
            $targetDayOfMonth = (int)$matches[3];
            
            $currentHour = (int)date('H', $currentTime);
            $currentMinute = (int)date('i', $currentTime);
            $currentDayOfMonth = (int)date('j', $currentTime); // 1-31
            $currentDay = date('Y-m-d', $currentTime);
            
            // Vérifier si on est le bon jour du mois
            if ($currentDayOfMonth !== $targetDayOfMonth) {
                return false;
            }
            
            // Vérifier si on est à la bonne heure/minute
            if ($currentHour !== $targetHour || $currentMinute !== $targetMinute) {
                return false;
            }
            
            // Si jamais exécuté, exécuter
            if ($lastExecution === null) {
                return true;
            }
            
            // Vérifier qu'on n'a pas déjà exécuté aujourd'hui
            $lastExecutionDay = date('Y-m-d', $lastExecution);
            return $currentDay !== $lastExecutionDay;
        }

        return false;
    }
}
