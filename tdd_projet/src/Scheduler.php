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
     * @return void
     * @throws \InvalidArgumentException Si une tâche avec ce nom existe déjà
     */
    public function scheduleTask(string $name, callable $callback, string $periodicity = '*'): void
    {
        if (isset($this->tasks[$name])) {
            throw new \InvalidArgumentException("Task with name '{$name}' already exists.");
        }

        $this->tasks[$name] = [
            'callback' => $callback,
            'periodicity' => $periodicity,
            'lastExecution' => null
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
     * Exécute les tâches qui doivent l'être maintenant
     * 
     * @return void
     */
    public function tick(): void
    {
        $currentTime = $this->timeProvider->getCurrentTime();
        
        foreach ($this->tasks as $name => &$task) {
            if ($this->shouldExecute($task, $currentTime)) {
                call_user_func($task['callback']);
                $task['lastExecution'] = $currentTime;
            }
        }
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
        // Si jamais exécutée, l'exécuter
        if ($task['lastExecution'] === null) {
            return true;
        }

        $periodicity = $task['periodicity'];
        $elapsed = $currentTime - $task['lastExecution'];

        // Pour '*' (chaque minute) : vérifier si 60 secondes sont passées
        if ($periodicity === '*') {
            return $elapsed >= 60;
        }

        // Pour '*/N' (toutes les N minutes) : vérifier si N*60 secondes sont passées
        if (preg_match('/^\*\/(\d+)$/', $periodicity, $matches)) {
            $minutes = (int)$matches[1];
            return $elapsed >= ($minutes * 60);
        }

        return false;
    }
}
