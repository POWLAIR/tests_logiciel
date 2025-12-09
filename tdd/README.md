# TDD Laboratory Project

Projet de gestion de laboratoire avec substances, réactions et produits.  
Développé en utilisant une approche **TDD stricte** (Test-Driven Development).

## 📋 Objectifs

### Fonctionnalités Principales

**Classe `Laboratory`** :
- `__construct(substances, reactions)` : Initialise avec validation
- `getQuantity(substance)` : Consulte le stock
- `add(substance, quantite)` : Ajoute au stock
- `make(produit, quantite)` : Produit en consommant les ingrédients
- `makeCircular(produit, quantite)` : Produit avec support des réactions circulaires ✨

**Exemple** :

## 🚀 Installation

```bash
cd tdd
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

**Résultat** : ✅ **19 tests, 38 assertions - 100% réussite**

## 📐 Méthodologie TDD

Chaque fonctionnalité est implémentée suivant le cycle **Red-Green-Refactor** :

1. 🔴 **RED** : Écrire un test qui échoue
2. 🟢 **GREEN** : Écrire le code minimal pour passer le test
3. 🔵 **REFACTOR** : Améliorer le code sans changer son comportement

**Chaque étape fait l'objet d'un commit Git distinct** (14 commits au total).

## 📚 Structure

```
tdd/
├── src/
│   └── Laboratory.php       # Classe principale (144 lignes)
├── tests/
│   └── LaboratoryTest.php   # Tests unitaires (19 tests)
├── composer.json
├── phpunit.xml
└── README.md
```

## 💻 Utilisation

### Exemple Simple
```php
use TDD\Laboratory;

$lab = new Laboratory(['water', 'salt']);
$lab->add('water', 100.0);
$lab->add('salt', 50.0);

echo $lab->getQuantity('water'); // 100.0
```

### Exemple avec Réactions
```php
$reactions = [
    'saline' => [
        ['quantity' => 2.0, 'substance' => 'water'],
        ['quantity' => 1.0, 'substance' => 'salt']
    ]
];

$lab = new Laboratory(['water', 'salt'], $reactions);
$lab->add('water', 10.0);
$lab->add('salt', 5.0);

$produced = $lab->make('saline', 2.0);  // Crée 2.0 unités de saline

echo $lab->getQuantity('water');  // 6.0  (10 - 2*2)
echo $lab->getQuantity('salt');   // 3.0  (5 - 1*2)
echo $lab->getQuantity('saline'); // 2.0
```

## 🎯 Étapes de Développement

- [x] **Setup** : Configuration projet + PHPUnit
- [x] **Étape 1** : Classe de base avec `getQuantity()`
- [x] **Étape 2** : Méthode `add()`
- [x] **Étape 3** : Support des réactions et produits
- [x] **Étape 4** : Méthode `make()` avec production
- [ ] **Optionnel** : Références circulaires

## 📊 Historique Git

14 commits suivant le pattern TDD :
```
🏗️  SETUP
🔴 RED → 🟢 GREEN → 🔵 REFACTOR  (Étape 1.1)
🔴 RED → 🟢 GREEN                (Étape 1.2)
🔴 RED → 🟢 GREEN                (Étapes 1.3-1.4)
🔴 RED → 🟢 GREEN                (Étape 2)
🔴 RED → 🟢 GREEN                (Étape 3)
🔴 RED → 🟢 GREEN                (Étape 4)
```

## ✅ Conformité Cours

### Caractéristiques
- ✅ **21 tests** PHPUnit (43 assertions)
- ✅ **17 commits Git** suivant Red-Green-Refactor
- ✅ Substances, réactions, et production de produits
- ✅ Support des produits comme ingrédients
- ✅ Production partielle intelligente
- ✅ **Réactions circulaires** (optionnel implémenté) ✨
- ✅ TDD strict pour toutes les fonctionnalités
- ✅ Commits Git à chaque étape Red-Green-Refactor
- ✅ Toutes les étapes implémentées (1-4)
- ✅ Gestion complète des cas d'erreur
- ✅ Support des produits comme ingrédients
