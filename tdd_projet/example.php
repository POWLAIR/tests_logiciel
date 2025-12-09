<?php

require_once __DIR__ . '/vendor/autoload.php';

use Scheduler\Scheduler;

echo "=== Scheduler TDD - Exemple d'utilisation ===\n\n";

$scheduler = new Scheduler();

// Compteur pour chaque tâche
$backupCount = 0;
$cleanupCount = 0;
$reportCount = 0;

// Tâche 1 : Sauvegarde chaque minute
$scheduler->scheduleTask('backup', function() use (&$backupCount) {
    $backupCount++;
    echo "[" . date('H:i:s') . "] ✅ Sauvegarde #{$backupCount} effectuée\n";
}, '*');

// Tâche 2 : Nettoyage toutes les 2 minutes
$scheduler->scheduleTask('cleanup', function() use (&$cleanupCount) {
    $cleanupCount++;
    echo "[" . date('H:i:s') . "] 🧹 Nettoyage #{$cleanupCount} effectué\n";
}, '*/2');

// Tâche 3 : Rapport toutes les 5 minutes
$scheduler->scheduleTask('report', function() use (&$reportCount) {
    $reportCount++;
    echo "[" . date('H:i:s') . "] 📊 Rapport #{$reportCount} généré\n";
}, '*/5');

echo "📋 Tâches planifiées : " . count($scheduler->getTasks()) . "\n\n";
echo "⏰ Simulation de 10 minutes (tick toutes les minutes)...\n\n";

// Simulation avec MockTimeProvider pour démonstration rapide
$timeProvider = new \Scheduler\Tests\Mocks\MockTimeProvider(0);
$scheduler = new Scheduler($timeProvider);

// Re-planifier avec le nouveau scheduler
$backupCount = $cleanupCount = $reportCount = 0;

$scheduler->scheduleTask('backup', function() use (&$backupCount) {
    $backupCount++;
    echo "[T+" . ($GLOBALS['currentMinute']) . "min] ✅ Sauvegarde #{$backupCount}\n";
}, '*');

$scheduler->scheduleTask('cleanup', function() use (&$cleanupCount) {
    $cleanupCount++;
    echo "[T+" . ($GLOBALS['currentMinute']) . "min] 🧹 Nettoyage #{$cleanupCount}\n";
}, '*/2');

$scheduler->scheduleTask('report', function() use (&$reportCount) {
    $reportCount++;
    echo "[T+" . ($GLOBALS['currentMinute']) . "min] 📊 Rapport #{$reportCount}\n";
}, '*/5');

// Simulation de 10 minutes
for ($minute = 0; $minute <= 10; $minute++) {
    $GLOBALS['currentMinute'] = $minute;
    $scheduler->tick();
    $timeProvider->advanceTime(60); // Avancer d'une minute
}

echo "\n📈 Résumé après 10 minutes :\n";
echo "   • Sauvegardes : {$backupCount}\n";
echo "   • Nettoyages  : {$cleanupCount}\n";
echo "   • Rapports    : {$reportCount}\n";

echo "\n✅ Démonstration terminée !\n";
