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
}
