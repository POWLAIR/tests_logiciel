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

    /**
     * 🔴 RED - Iteration 8.1
     * Lever une exception si une tâche avec le même nom existe déjà
     */
    public function testThrowsExceptionWhenSchedulingDuplicateTaskName(): void
    {
        $scheduler = new Scheduler();
        $callback = function() {};
        
        $scheduler->scheduleTask('my-task', $callback);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already exists');
        
        // Tenter de planifier une tâche avec le même nom doit lever une exception
        $scheduler->scheduleTask('my-task', $callback);
    }

    /**
     * 🔴 RED - Iteration 9.1
     * tick() gère correctement plusieurs tâches avec périodicités différentes
     */
    public function testTickHandlesMultipleTasksWithDifferentPeriodicities(): void
    {
        $timeProvider = new \Scheduler\Tests\Mocks\MockTimeProvider(0);
        $scheduler = new Scheduler($timeProvider);
        
        $count1 = 0;
        $count2 = 0;
        $count3 = 0;
        
        $callback1 = function() use (&$count1) { $count1++; };
        $callback2 = function() use (&$count2) { $count2++; };
        $callback3 = function() use (&$count3) { $count3++; };
        
        // 3 tâches avec périodicités différentes
        $scheduler->scheduleTask('every-minute', $callback1, '*');
        $scheduler->scheduleTask('every-2-minutes', $callback2, '*/2');
        $scheduler->scheduleTask('every-5-minutes', $callback3, '*/5');
        
        // T=0 : toutes s'exécutent
        $scheduler->tick();
        $this->assertEquals(1, $count1);
        $this->assertEquals(1, $count2);
        $this->assertEquals(1, $count3);
        
        // T=60s (1 min) : seule 'every-minute' s'exécute
        $timeProvider->advanceTime(60);
        $scheduler->tick();
        $this->assertEquals(2, $count1);
        $this->assertEquals(1, $count2);
        $this->assertEquals(1, $count3);
        
        // T=120s (2 min) : 'every-minute' et 'every-2-minutes'
        $timeProvider->advanceTime(60);
        $scheduler->tick();
        $this->assertEquals(3, $count1);
        $this->assertEquals(2, $count2);
        $this->assertEquals(1, $count3);
        
        // T=180s (3 min) : seule 'every-minute'
        $timeProvider->advanceTime(60);
        $scheduler->tick();
        $this->assertEquals(4, $count1);
        $this->assertEquals(2, $count2);
        $this->assertEquals(1, $count3);
        
        // T=240s (4 min) : 'every-minute' et 'every-2-minutes'
        $timeProvider->advanceTime(60);
        $scheduler->tick();
        $this->assertEquals(5, $count1);
        $this->assertEquals(3, $count2);
        $this->assertEquals(1, $count3);
        
        // T=300s (5 min) : toutes s'exécutent
        $timeProvider->advanceTime(60);
        $scheduler->tick();
        $this->assertEquals(6, $count1);
        $this->assertEquals(3, $count2); // Note: elle a déjà exécuté à 240s, donc pas à 300s
        $this->assertEquals(2, $count3);
    }

    /**
     * 🔴 RED - Iteration 10.1
     * tick() exécute les tâches à heures fixes (0 H * * *)
     */
    public function testTickExecutesTasksAtFixedHour(): void
    {
        // Commencer à 8h00 le 2025-01-15
        $baseTime = strtotime('2025-01-15 08:00:00');
        $timeProvider = new \Scheduler\Tests\Mocks\MockTimeProvider($baseTime);
        $scheduler = new Scheduler($timeProvider);
        
        $executionCount = 0;
        $callback = function() use (&$executionCount) {
            $executionCount++;
        };
        
        // Tâche programmée pour 9h00 tous les jours (0 9 * * *)
        $scheduler->scheduleTask('daily-9am', $callback, '0 9 * * *');
        
        // 8h00 : ne doit PAS exécuter
        $scheduler->tick();
        $this->assertEquals(0, $executionCount, "Ne devrait pas exécuter à 8h");
        
        // Avancer à 9h00 : DOIT exécuter
        $timeProvider->setCurrentTime(strtotime('2025-01-15 09:00:00'));
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Devrait exécuter à 9h");
        
        // 9h30 le même jour : ne doit PAS exécuter (déjà fait aujourd'hui)
        $timeProvider->setCurrentTime(strtotime('2025-01-15 09:30:00'));
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Ne devrait pas réexécuter le même jour");
        
        // 10h00 le même jour : ne doit PAS exécuter
        $timeProvider->setCurrentTime(strtotime('2025-01-15 10:00:00'));
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Ne devrait pas exécuter à 10h");
        
        // 9h00 le lendemain : DOIT exécuter
        $timeProvider->setCurrentTime(strtotime('2025-01-16 09:00:00'));
        $scheduler->tick();
        $this->assertEquals(2, $executionCount, "Devrait exécuter le lendemain à 9h");
    }

    /**
     * 🔴 RED - Iteration 11.1
     * tick() exécute les tâches à jour de la semaine spécifique (0 H * * D)
     */
    public function testTickExecutesTasksOnSpecificDayOfWeek(): void
    {
        // 2025-01-13 = Lundi à 8h00
        $baseTime = strtotime('2025-01-13 08:00:00'); // Monday
        $timeProvider = new \Scheduler\Tests\Mocks\MockTimeProvider($baseTime);
        $scheduler = new Scheduler($timeProvider);
        
        $executionCount = 0;
        $callback = function() use (&$executionCount) {
            $executionCount++;
        };
        
        // Tâche programmée pour lundis à 9h00 (0 9 * * 1)
        // 0=Dimanche, 1=Lundi, 2=Mardi, ..., 6=Samedi
        $scheduler->scheduleTask('monday-9am', $callback, '0 9 * * 1');
        
        // Lundi 8h00 : ne doit PAS exécuter (pas encore 9h)
        $scheduler->tick();
        $this->assertEquals(0, $executionCount, "Ne devrait pas exécuter avant 9h");
        
        // Lundi 9h00 : DOIT exécuter
        $timeProvider->setCurrentTime(strtotime('2025-01-13 09:00:00'));
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Devrait exécuter lundi à 9h");
        
        // Mardi 9h00 : ne doit PAS exécuter (pas un lundi)
        $timeProvider->setCurrentTime(strtotime('2025-01-14 09:00:00'));
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Ne devrait pas exécuter mardi");
        
        // Mercredi 9h00 : ne doit PAS exécuter
        $timeProvider->setCurrentTime(strtotime('2025-01-15 09:00:00'));
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Ne devrait pas exécuter mercredi");
        
        // Lundi suivant 9h00 : DOIT exécuter
        $timeProvider->setCurrentTime(strtotime('2025-01-20 09:00:00'));
        $scheduler->tick();
        $this->assertEquals(2, $executionCount, "Devrait exécuter lundi suivant à 9h");
    }

    /**
     * 🔴 RED - Iteration 12.1
     * Peut mettre à jour une tâche existante (callback et/ou périodicité)
     */
    public function testCanUpdateExistingTask(): void
    {
        $scheduler = new Scheduler();
        
        $count1 = 0;
        $count2 = 0;
        
        $callback1 = function() use (&$count1) { $count1++; };
        $callback2 = function() use (&$count2) { $count2++; };
        
        // Planifier une tâche initiale
        $scheduler->scheduleTask('my-task', $callback1, '*');
        
        // Mettre à jour la tâche avec un nouveau callback et périodicité
        $scheduler->updateTask('my-task', $callback2, '*/5');
        
        $tasks = $scheduler->getTasks();
        $this->assertCount(1, $tasks, "Devrait avoir 1 seule tâche");
        $this->assertArrayHasKey('my-task', $tasks);
        
        // Vérifier que la périodicité a bien été mise à jour
        $this->assertEquals('*/5', $tasks['my-task']['periodicity']);
    }

    /**
     * 🔴 RED - Iteration 13.1
     * tick() exécute les tâches à jour du mois spécifique (0 H D * *)
     */
    public function testTickExecutesTasksOnSpecificDayOfMonth(): void
    {
        // Test : tâche le 1er de chaque mois à 9h (0 9 1 * *)
        // 2025-01-01 09:00 = Mercredi
        $timeProvider = new \Scheduler\Tests\Mocks\MockTimeProvider(strtotime('2025-01-01 08:00:00'));
        $scheduler = new Scheduler($timeProvider);
        
        $executionCount = 0;
        $callback = function() use (&$executionCount) {
            $executionCount++;
        };
        
        // Tâche programmée pour le 1er de chaque mois à 9h (0 9 1 * *)
        $scheduler->scheduleTask('monthly-report', $callback, '0 9 1 * *');
        
        // 1er janvier à 8h : ne doit PAS exécuter (pas encore 9h)
        $scheduler->tick();
        $this->assertEquals(0, $executionCount, "Ne devrait pas exécuter à 8h");
        
        // 1er janvier à 9h : DOIT exécuter
        $timeProvider->setCurrentTime(strtotime('2025-01-01 09:00:00'));
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Devrait exécuter le 1er à 9h");
        
        // 2 janvier à 9h : ne doit PAS exécuter (pas le 1er)
        $timeProvider->setCurrentTime(strtotime('2025-01-02 09:00:00'));
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Ne devrait pas exécuter le 2");
        
        // 15 janvier à 9h : ne doit PAS exécuter (pas le 1er)
        $timeProvider->setCurrentTime(strtotime('2025-01-15 09:00:00'));
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Ne devrait pas exécuter le 15");
        
        // 1er février à 9h : DOIT exécuter (1er du mois suivant)
        $timeProvider->setCurrentTime(strtotime('2025-02-01 09:00:00'));
        $scheduler->tick();
        $this->assertEquals(2, $executionCount, "Devrait exécuter le 1er février");
        
        // 1er février à 9h30 : ne doit PAS réexécuter (déjà fait aujourd'hui)
        $timeProvider->setCurrentTime(strtotime('2025-02-01 09:30:00'));
        $scheduler->tick();
        $this->assertEquals(2, $executionCount, "Ne devrait pas réexécuter le même jour");
    }

    /**
     * 🔴 RED - Iteration 14.1
     * Peut planifier une tâche one-time à une date/heure spécifique
     */
    public function testCanScheduleOneTimeTask(): void
    {
        $timeProvider = new \Scheduler\Tests\Mocks\MockTimeProvider(strtotime('2025-01-15 08:00:00'));
        $scheduler = new Scheduler($timeProvider);
        
        $executionCount = 0;
        $callback = function() use (&$executionCount) {
            $executionCount++;
        };
        
        // Format : @YYYY-MM-DD HH:MM (tâche unique)
        $scheduler->scheduleTask('one-time-meeting', $callback, '@2025-01-15 14:00');
        
        // 8h : ne doit PAS exécuter (pas encore l'heure)
        $scheduler->tick();
        $this->assertEquals(0, $executionCount, "Ne devrait pas exécuter à 8h");
        
        // 14h : DOIT exécuter (heure exacte)
        $timeProvider->setCurrentTime(strtotime('2025-01-15 14:00:00'));
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Devrait exécuter à 14h");
        
        // 14h01 : ne doit PAS réexécuter (c'est une tâche one-time)
        $timeProvider->setCurrentTime(strtotime('2025-01-15 14:01:00'));
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Ne devrait JAMAIS réexécuter (one-time)");
        
        // Lendemain même heure : ne doit PAS exécuter
        $timeProvider->setCurrentTime(strtotime('2025-01-16 14:00:00'));
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Ne devrait pas exécuter le lendemain");
        
        // Vérifier que la tâche existe toujours dans la liste
        $tasks = $scheduler->getTasks();
        $this->assertArrayHasKey('one-time-meeting', $tasks);
    }

    /**
     * 🔴 RED - Iteration 15.1
     * Tâche one-time avec auto_remove se supprime après exécution
     */
    public function testOneTimeTaskWithAutoRemove(): void
    {
        $timeProvider = new \Scheduler\Tests\Mocks\MockTimeProvider(strtotime('2025-01-15 13:00:00'));
        $scheduler = new Scheduler($timeProvider);
        
        $executionCount = 0;
        $callback = function() use (&$executionCount) {
            $executionCount++;
        };
        
        // Planifier avec option auto_remove
        $scheduler->scheduleTask('temp-reminder', $callback, '@2025-01-15 14:00', true);
        
        // Vérifier que la tâche existe
        $tasks = $scheduler->getTasks();
        $this->assertArrayHasKey('temp-reminder', $tasks);
        $this->assertCount(1, $tasks);
        
        // Avancer à 14h et exécuter
        $timeProvider->setCurrentTime(strtotime('2025-01-15 14:00:00'));
        $scheduler->tick();
        $this->assertEquals(1, $executionCount, "Devrait exécuter une fois");
        
        // Vérifier que la tâche a été SUPPRIMÉE automatiquement
        $tasks = $scheduler->getTasks();
        $this->assertArrayNotHasKey('temp-reminder', $tasks, "Tâche devrait être auto-supprimée");
        $this->assertCount(0, $tasks);
    }
}
