<?php

namespace Scheduler;

/**
 * Interface pour fournir le temps actuel
 * Permet de mocker le temps dans les tests
 */
interface TimeProviderInterface
{
    /**
     * Retourne le timestamp actuel
     * 
     * @return int Timestamp Unix
     */
    public function getCurrentTime(): int;
}
