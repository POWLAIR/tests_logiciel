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

    /**
     * 🔴 RED - Iteration 5.1
     * Le Scheduler accepte un TimeProvider injectable
     */
    public function testSchedulerAcceptsTimeProvider(): void
    {
        $timeProvider = new \Scheduler\Tests\Mocks\MockTimeProvider(1000);
        $scheduler = new Scheduler($timeProvider);
        
        // Le scheduler doit accepter le TimeProvider sans erreur
        $this->assertInstanceOf(Scheduler::class, $scheduler);
    }

    /**
     * 🔴 RED - Iteration 6.1
     * tick() exécute les tâches "chaque minute"
     */
    public function testTickExecutesTasksEveryMinute(): void
    {
        $timeProvider = new \Scheduler\Tests\Mocks\MockTimeProvider(0);
        $scheduler = new Scheduler($timeProvider);
        
        $executionCount = 0;
        $callback = function() use (&$executionCount) {
            $executionCount++;
        };
        
        // Planifier une tâche "chaque minute"
        $scheduler->scheduleTask('every-minute-task', $callback, '*');
        
        // Tick au temps 0 : doit exécuter
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Devrait exécuter au premier tick");
        
        // Avancer de 30 secondes : ne doit PAS exécuter
        $timeProvider->advanceTime(30);
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Ne devrait pas exécuter après 30s");
        
        // Avancer de 30 secondes de plus (total 60s) : doit exécuter
        $timeProvider->advanceTime(30);
        $scheduler->tick();
        $this->assertEquals(2, $executionCount, "Devrait exécuter après 60s");
    }

    /**
     * 🔴 RED - Iteration 7.1
     * tick() exécute les tâches "toutes les N minutes"
     */
    public function testTickExecutesTasksEveryNMinutes(): void
    {
        $timeProvider = new \Scheduler\Tests\Mocks\MockTimeProvider(0);
        $scheduler = new Scheduler($timeProvider);
        
        $executionCount = 0;
        $callback = function() use (&$executionCount) {
            $executionCount++;
        };
        
        // Planifier une tâche "toutes les 5 minutes"
        $scheduler->scheduleTask('every-5-minutes', $callback, '*/5');
        
        // Premier tick : doit exécuter
        $scheduler->tick();
        $this->assertEquals(1, $executionCount);
        
        // Avancer de 4 minutes : ne doit PAS exécuter
        $timeProvider->advanceTime(4 * 60);
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Ne devrait pas exécuter après 4 min");
        
        // Avancer de 1 minute de plus (total 5 min) : doit exécuter
        $timeProvider->advanceTime(60);
        $scheduler->tick();
        $this->assertEquals(2, $executionCount, "Devrait exécuter après 5 min");
        
        // Avancer de 10 minutes : doit exécuter encore une fois
        $timeProvider->advanceTime(10 * 60);
        $scheduler->tick();
        $this->assertEquals(3, $executionCount, "Devrait exécuter après 15 min total");
    }
}
