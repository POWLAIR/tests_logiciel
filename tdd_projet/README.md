# Scheduler TDD Project

Projet de gestion de tâches planifiées avec support de périodicités type cron.  
Développé en utilisant une approche **TDD stricte** (Test-Driven Development).

## 📋 Objectifs

### Fonctionnalités Principales

**Classe `Scheduler`** :
- ✅ `getTasks()` : Énumère les tâches planifiées
- ✅ `scheduleTask($name, $callback, $periodicity)` : Définit/modifie une tâche
- ✅ `removeTask($name)` : Supprime une tâche par nom
- ✅ `tick()` : Exécute les tâches dues à l'instant actuel
- ✅ **TimeProvider injectable** : Tests déterministes

### Périodicités Supportées

- ✅ `*` : Chaque minute
- ✅ `*/N` : Toutes les N minutes (ex: `*/5` = toutes les 5 minutes)
- 🔜 Heures fixes (ex: `0 9 * * *`)
- 🔜 Jours de la semaine (ex: `0 9 * * 1`)

## 🚀 Installation

```bash
cd tdd_projet
composer install
```

## 🧪 Tests

```bash
composer test
# ou
php vendor/bin/phpunit

# Avec documentation des tests
php vendor/bin/phpunit --testdox
```

**Résultat actuel** : ✅ **8 tests, 36 assertions - 100% réussite**

```
Scheduler (Scheduler\Tests\Scheduler)
 ✔ Scheduler starts with no tasks
 ✔ Can schedule simple task
 ✔ Can remove task
 ✔ Scheduler accepts time provider
 ✔ Tick executes tasks every minute
 ✔ Tick executes tasks every n minutes
 ✔ Throws exception when scheduling duplicate task name
 ✔ Tick handles multiple tasks with different periodicities
```

## 📐 Méthodologie TDD

Chaque fonctionnalité est implémentée suivant le cycle **Red-Green-Refactor** :

1. 🔴 **RED** : Écrire un test qui échoue
2. 🟢 **GREEN** : Écrire le code minimal pour passer le test
3. 🔵 **REFACTOR** : Améliorer le code sans changer son comportement

**Chaque étape fait l'objet d'un commit Git distinct** (23 commits actuellement).

## 📚 Structure

```
tdd_projet/
├── src/
│   ├── Scheduler.php              # Classe principale (118 lignes)
│   ├── TimeProviderInterface.php  # Interface temps injectable
│   └── SystemTimeProvider.php     # Implémentation temps réel
├── tests/
│   ├── SchedulerTest.php          # Tests unitaires (8 tests)
│   └── Mocks/
│       ├── MockTimeProvider.php   # Mock pour contrôler le temps
│       └── MockCallback.php       # Mock pour compter exécutions
├── composer.json
├── phpunit.xml
└── README.md
```

## 💻 Utilisation

### Exemple Simple

```php
use Scheduler\Scheduler;

$scheduler = new Scheduler();

// Planifier une tâche chaque minute
$scheduler->scheduleTask('backup', function() {
    echo "Sauvegarde effectuée\n";
}, '*');

// Planifier une tâche toutes les 5 minutes
$scheduler->scheduleTask('cleanup', function() {
    echo "Nettoyage effectué\n";
}, '*/5');

// Dans une boucle infinie (daemon)
while (true) {
    $scheduler->tick(); // Exécute les tâches dues
    sleep(60); // Attendre 1 minute
}
```

### Avec TimeProvider personnalisé (tests)

```php
use Scheduler\Scheduler;
use Scheduler\Tests\Mocks\MockTimeProvider;

$timeProvider = new MockTimeProvider(0);
$scheduler = new Scheduler($timeProvider);

$executionCount = 0;
$scheduler->scheduleTask('task', function() use (&$executionCount) {
    $executionCount++;
}, '*');

$scheduler->tick(); // Exécute
echo $executionCount; // 1

$timeProvider->advanceTime(60); // Avancer de 1 minute
$scheduler->tick(); // Exécute à nouveau
echo $executionCount; // 2
```

## 🎯 Étapes de Développement

- [x] **Setup** : Configuration projet + PHPUnit
- [x] **Étape 1** : Classe de base avec `getTasks()`
- [x] **Étape 2** : Méthode `scheduleTask()` et `removeTask()`
- [x] **Étape 3** : Injection TimeProvider (tests déterministes)
- [x] **Étape 4** : Méthode `tick()` avec exécution
- [x] **Étape 5** : Périodicité "chaque minute" (`*`)
- [x] **Étape 6** : Périodicité "toutes les N minutes" (`*/N`)
- [x] **Étape 7** : Validation (noms uniques)
- [x] **Étape 8** : Tests multi-tâches
- [ ] **Étape 9+** : Périodicités avancées (heures, jours, cron complet)

## 📊 Historique Git

23 commits suivant le pattern TDD :

```bash
git log --oneline --graph
```

```
🏗️  SETUP
🔴 RED → 🟢 GREEN → 🔵 REFACTOR  (getTasks)
🔴 RED → 🟢 GREEN → 🔵 REFACTOR  (scheduleTask)
🔴 RED → 🟢 GREEN → 🔵 REFACTOR  (removeTask)
🔴🟢 RED+GREEN → 🔵 REFACTOR     (TimeProvider)
🔴 RED → 🟢 GREEN → 🔵 REFACTOR  (tick + every minute)
🔴 RED → 🟢 GREEN → 🔵 REFACTOR  (every N minutes)
🔴 RED → 🟢 GREEN → 🔵 REFACTOR  (validation)
🔴🟢 RED+GREEN → 🔵 REFACTOR     (multi-tasks)
```

## ✅ Conformité Cours

### Caractéristiques

- ✅ **8 tests unitaires** PHPUnit (36 assertions)
- ✅ **23 commits Git** suivant Red-Green-Refactor
- ✅ Gestion complète des tâches planifiées
- ✅ Support de périodicités multiples
- ✅ TimeProvider injectable pour tests déterministes
- ✅ Validation et gestion d'erreurs
- ✅ TDD strict avec commits à chaque étape
- ✅ Code propre et bien documenté
- ✅ 100% des tests passent

## 🔮 Améliorations Futures

- Support heures fixes (ex: `0 9 * * *` = 9h tous les jours)
- Support jours de la semaine (ex: `0 9 * * 1` = lundis à 9h)
- Parser cron complet (5 champs)
- Gestion des exceptions dans les callbacks
- Logs des exécutions
- Interface graphique web (bonus démo)

---

**Atelier EFREI - Tests Logiciels**  
Projet réalisé en suivant rigoureusement la méthodologie TDD.
