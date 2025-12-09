# Workspace Tests Logiciel

Workspace contenant **deux projets PHP** pour le cours de tests logiciels.

---

## 📁 Structure

```
tests_logiciel/
├── projet-officine/     # Gestion d'officine avec recettes circulaires
└── tdd/                 # Laboratory développé en TDD strict
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

- ✅ **19 tests** (38 assertions)
- ✅ **15 commits** Red-Green-Refactor
- ✅ Production de produits par réactions

```bash
cd tdd
php vendor/bin/phpunit
```

📖 Détails : [`tdd/README.md`](tdd/README.md)

---

## 🚀 Quick Start

### Installation
```bash
cd projet-officine && composer install
cd ../tdd && composer install
```

### Tests Complets
```bash
cd projet-officine && php vendor/bin/phpunit
cd ../tdd && php vendor/bin/phpunit
```

**Résultat** : ✅ **55 tests** réussis (135 assertions)

---

## 🎯 Comparaison

| | Officine | Laboratory |
|---|---|---|
| **Méthodologie** | Tests après code | TDD strict |
| **Tests** | 36 (97 assertions) | 19 (38 assertions) |
| **Spécialité** | Recettes circulaires | Red-Green-Refactor |
