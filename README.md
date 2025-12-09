# Workspace Tests Logiciel

Workspace contenant **trois projets PHP** pour le cours de tests logiciels.

---

## 📁 Structure

```
tests_logiciel/
├── projet-officine/     # Gestion d'officine avec recettes circulaires
├── tdd/                 # Laboratory développé en TDD strict
└── tdd_projet/          # Scheduler avec périodicités cron (TDD strict)
```

---

## 🧪 Projet 1 : Officine

Système de gestion d'officine de potions magiques.

- ✅ **36 tests** (97 assertions)
- ✅ Support des recettes circulaires
- ✅ Gestion complète du stock

```bash
cd projet-officine
php vendor/bin/phpunit
```

📖 Détails : [`projet-officine/README.md`](projet-officine/README.md)

---

## 🔬 Projet 2 : Laboratory (TDD)

Système de laboratoire développé en **TDD strict**.

- ✅ **21 tests** (43 assertions)
- ✅ **18 commits** Red-Green-Refactor
- ✅ Production de produits par réactions
- ✅ Support réactions circulaires

```bash
cd tdd
php vendor/bin/phpunit
```

📖 Détails : [`tdd/README.md`](tdd/README.md)

---

## ⏰ Projet 3 : Scheduler (TDD Strict)

Gestionnaire de tâches planifiées avec périodicités cron.

- ✅ **11 tests** (49 assertions)
- ✅ **40 commits** Red-Green-Refactor
- ✅ 4 types de périodicités (`*`, `*/N`, heures, jours semaine)
- ✅ Interface web interactive moderne

```bash
cd tdd_projet
php vendor/bin/phpunit
```

📖 Détails : [`tdd_projet/README.md`](tdd_projet/README.md)  
🎨 Démo UI : [`tdd_projet/demo/`](tdd_projet/demo/)

---

## 🚀 Quick Start

### Installation
```bash
cd projet-officine && composer install
cd ../tdd && composer install
cd ../tdd_projet && composer install
```

### Tests Complets
```bash
cd projet-officine && php vendor/bin/phpunit
cd ../tdd && php vendor/bin/phpunit
cd ../tdd_projet && php vendor/bin/phpunit
```

**Résultat** : ✅ **68 tests** réussis (189 assertions)

---

## 🎯 Comparaison

| | Officine | Laboratory | Scheduler |
|---|---|---|---|
| **Méthodologie** | Tests après code | TDD strict | TDD strict |
| **Tests** | 36 (97 assertions) | 21 (43 assertions) | 11 (49 assertions) |
| **Commits Git** | - | 18 (R-G-R) | 40 (R-G-R) |
| **Spécialité** | Recettes circulaires | Production réactions | Périodicités cron |
| **UI** | - | - | ✅ Interface web |
