<?php

namespace Scheduler;

/**
 * Implémentation système du TimeProvider
 * Retourne le temps réel du système
 */
class SystemTimeProvider implements TimeProviderInterface
{
    public function getCurrentTime(): int
    {
        return time();
    }
}
