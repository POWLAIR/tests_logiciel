<?php

namespace Scheduler\Tests;

use PHPUnit\Framework\TestCase;
use Scheduler\Scheduler;

class SchedulerTest extends TestCase
{
    /**
     * 🔴 RED - Iteration 2.1
     * Le Scheduler doit démarrer sans aucune tâche planifiée
     */
    public function testSchedulerStartsWithNoTasks(): void
    {
        $scheduler = new Scheduler();
        
        $tasks = $scheduler->getTasks();
        
        $this->assertIsArray($tasks);
        $this->assertCount(0, $tasks);
    }

    /**
     * 🔴 RED - Iteration 3.1
     * Peut ajouter une tâche simple avec un nom et un callback
     */
    public function testCanScheduleSimpleTask(): void
    {
        $scheduler = new Scheduler();
        $callback = function() {
            return "Task executed";
        };
        
        $scheduler->scheduleTask('my-task', $callback);
        
        $tasks = $scheduler->getTasks();
        $this->assertCount(1, $tasks);
        $this->assertArrayHasKey('my-task', $tasks);
    }

    /**
     * 🔴 RED - Iteration 4.1
     * Peut supprimer une tâche planifiée par son nom
     */
    public function testCanRemoveTask(): void
    {
        $scheduler = new Scheduler();
        $callback = function() { return "test"; };
        
        $scheduler->scheduleTask('task1', $callback);
        $scheduler->scheduleTask('task2', $callback);
        
        $this->assertCount(2, $scheduler->getTasks());
        
        $scheduler->removeTask('task1');
        
        $tasks = $scheduler->getTasks();
        $this->assertCount(1, $tasks);
        $this->assertArrayNotHasKey('task1', $tasks);
        $this->assertArrayHasKey('task2', $tasks);
    }
}
