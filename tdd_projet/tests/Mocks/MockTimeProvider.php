<?php

namespace Scheduler\Tests\Mocks;

use Scheduler\TimeProviderInterface;

/**
 * Mock du TimeProvider pour les tests
 * Permet de fixer le temps à une valeur spécifique
 */
class MockTimeProvider implements TimeProviderInterface
{
    private int $currentTime;

    public function __construct(int $initialTime = 0)
    {
        $this->currentTime = $initialTime;
    }

    public function getCurrentTime(): int
    {
        return $this->currentTime;
    }

    /**
     * Définit le temps actuel
     */
    public function setCurrentTime(int $time): void
    {
        $this->currentTime = $time;
    }

    /**
     * Avance le temps de N secondes
     */
    public function advanceTime(int $seconds): void
    {
        $this->currentTime += $seconds;
    }
}
