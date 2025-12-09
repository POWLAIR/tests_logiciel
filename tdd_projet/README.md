# Scheduler TDD Project

Projet de gestion de tâches planifiées avec support de périodicités type cron.  
Développé en utilisant une approche **TDD stricte** (Test-Driven Development).

## 📋 Objectifs

### Fonctionnalités Principales

**Classe `Scheduler`** :
- Gestion de tâches planifiées
- Support de multiples périodicités (cron-like)
- Exécution périodique des tâches dues
- Injection de dépendances (TimeProvider pour tests déterministes)

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

## 📐 Méthodologie TDD

Chaque fonctionnalité est implémentée suivant le cycle **Red-Green-Refactor** :

1. 🔴 **RED** : Écrire un test qui échoue
2. 🟢 **GREEN** : Écrire le code minimal pour passer le test
3. 🔵 **REFACTOR** : Améliorer le code sans changer son comportement

**Chaque étape fait l'objet d'un commit Git distinct**.

## 📚 Structure

```
tdd_projet/
├── src/
│   └── Scheduler.php       # Classe principale (à venir)
├── tests/
│   └── SchedulerTest.php   # Tests unitaires (à venir)
├── composer.json
├── phpunit.xml
└── README.md
```

## 🎯 Périodicités Supportées

À implémenter progressivement :
- [ ] Chaque minute (`* * * * *`)
- [ ] Toutes les N minutes
- [ ] Heures fixes (`0 9 * * *`)
- [ ] Jours de la semaine (`0 9 * * 1`)
- [ ] Syntaxe cron complète

## 📊 Progression

- [x] Setup projet
- [ ] Tests et implémentation en cours...

---

**Atelier EFREI - Tests Logiciels**
