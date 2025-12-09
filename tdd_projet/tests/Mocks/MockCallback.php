<?php

namespace Scheduler\Tests\Mocks;

/**
 * Mock d'un callback pour compter les exécutions
 */
class MockCallback
{
    public int $executionCount = 0;
    public array $executionTimes = [];

    public function __invoke(): void
    {
        $this->executionCount++;
        $this->executionTimes[] = time();
    }

    public function reset(): void
    {
        $this->executionCount = 0;
        $this->executionTimes = [];
    }
}
