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
     * @return void
     */
    public function scheduleTask(string $name, callable $callback): void
    {
        $this->tasks[$name] = [
            'callback' => $callback
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
}
