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
}
