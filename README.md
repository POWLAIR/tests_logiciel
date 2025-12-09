# Workspace Tests Logiciel

Workspace contenant deux projets PHP développés pour le cours de tests logiciels.  
Chaque projet utilise **PHPUnit** et suit des méthodologies de développement rigoureuses.

---

## 📁 Structure du Workspace

```
tests_logiciel/
├── projet-officine/     # Projet 1 : Gestion d'officine
│   ├── src/
│   ├── tests/
│   ├── examples/
│   └── README.md
├── tdd/                 # Projet 2 : Laboratory (TDD)
│   ├── src/
│   ├── tests/
│   └── README.md
└── README.md           # Ce fichier
```

---

## 🧪 Projet 1 : Officine (projet-officine/)

**Description** : Système de gestion d'officine de potions magiques avec support des recettes circulaires.

### Caractéristiques
- ✅ **36 tests** PHPUnit (97 assertions)
- ✅ Gestion de stock de substances
- ✅ Création de potions via recettes
- ✅ Support complet des **recettes circulaires**
- ✅ Validation et gestion d'erreurs robuste

### Utilisation
```bash
cd projet-officine

# Lancer les tests
php vendor/bin/phpunit

# Voir un exemple
php examples/exemple.php
php examples/recettes_circulaires_exemple.php
```

### Fonctionnalités Principales

**Classe `Officine`** :
- `ajouterStock(substance, quantite)` : Ajoute du stock
- `getStock(substance)` : Consulte le stock
- `preparer(recette, quantite)` : Prépare une potion
- `preparerCirculaire(recette, quantite)` : Gère les dépendances circulaires

**Exemple** :
```php
$officine = new Officine(['eau', 'sel']);
$officine->ajouterStock('eau', 100);
$officine->ajouterStock('sel', 50);

$recettes = [
    'salin' => [['quantite' => 2, 'ingredient' => 'eau'],
                ['quantite' => 1, 'ingredient' => 'sel']]
];

$officine->preparer('salin', 5, $recettes); // Crée 5 unités
```

📖 **Documentation complète** : [`projet-officine/README.md`](projet-officine/README.md)

---

## 🔬 Projet 2 : Laboratory (tdd/)

**Description** : Système de laboratoire développé en **TDD strict** (Test-Driven Development).

### Caractéristiques
- ✅ **19 tests** PHPUnit (38 assertions)
- ✅ **15 commits Git** suivant Red-Green-Refactor
- ✅ Substances, réactions, et production de produits
- ✅ Support des produits comme ingrédients
- ✅ Production partielle intelligente

### Méthodologie TDD Appliquée

Chaque fonctionnalité suivant le cycle :
1. 🔴 **RED** : Test qui échoue → commit
2. 🟢 **GREEN** : Code minimal → commit
3. 🔵 **REFACTOR** : Amélioration → commit

### Utilisation
```bash
cd tdd

# Lancer les tests
php vendor/bin/phpunit

# Tests avec documentation
php vendor/bin/phpunit --testdox
```

### Fonctionnalités Principales

**Classe `Laboratory`** :
- `__construct(substances, reactions)` : Initialise avec validation
- `getQuantity(substance)` : Consulte le stock
- `add(substance, quantite)` : Ajoute au stock
- `make(produit, quantite)` : Produit en consommant les ingrédients

**Exemple** :
```php
use TDD\Laboratory;

$reactions = [
    'saline' => [
        ['quantity' => 2.0, 'substance' => 'water'],
        ['quantity' => 1.0, 'substance' => 'salt']
    ]
];

$lab = new Laboratory(['water', 'salt'], $reactions);
$lab->add('water', 10.0);
$lab->add('salt', 5.0);

$produced = $lab->make('saline', 2.0);  // Produit 2.0 unités
// Stock: water=6.0, salt=3.0, saline=2.0
```

📖 **Documentation complète** : [`tdd/README.md`](tdd/README.md)

---

## 🎯 Comparaison des Projets

| Aspect | Projet Officine | Projet Laboratory |
|--------|----------------|-------------------|
| **Méthodologie** | Tests après code | TDD strict (test-first) |
| **Tests** | 36 tests, 97 assertions | 19 tests, 38 assertions |
| **Commits Git** | Standard | 15 commits Red-Green-Refactor |
| **Fonctionnalité unique** | Recettes circulaires | Production partielle |
| **Complexité** | Avancée (fractionnaires, cycles) | Modulaire (extensible) |

---

## 🚀 Installation Globale

### Prérequis
- PHP 8.0+
- Composer

### Installation des Deux Projets
```bash
# Projet Officine
cd projet-officine
composer install

# Projet Laboratory
cd ../tdd
composer install
```

### Lancer Tous les Tests
```bash
# Depuis la racine du workspace
cd projet-officine && php vendor/bin/phpunit && cd ../tdd && php vendor/bin/phpunit
```

**Résultat attendu** :
- Projet Officine : ✅ 36 tests passent
- Projet Laboratory : ✅ 19 tests passent
- **Total : 55 tests réussis**

---

## 📚 Documentation Additionnelle

- **Officine** : Voir [`projet-officine/README.md`](projet-officine/README.md) pour API complète, exemples Java, et détails sur les recettes circulaires
- **Laboratory** : Voir [`tdd/README.md`](tdd/README.md) pour exemples d'utilisation, méthodologie TDD, et historique Git

---

## 🎓 Objectifs Pédagogiques

### Projet Officine
- Résolution de dépendances circulaires
- Gestion de quantités fractionnaires
- Tests de régression

### Projet Laboratory  
- Maîtrise du TDD (Red-Green-Refactor)
- Commits Git structurés
- Développement itératif
- Validation et gestion d'erreurs

---

## 📊 Statistiques

```
Workspace tests_logiciel/
├── 2 projets PHP
├── 55 tests unitaires
├── 135 assertions
├── 100% de réussite
└── Documentation complète
```

---

## ✨ Commandes Utiles

```bash
# Vérifier que tout fonctionne
cd projet-officine && composer test
cd ../tdd && composer test

# Voir l'historique Git du TDD
cd tdd && git log --oneline --graph

# Lancer un exemple
cd projet-officine && php examples/exemple.php
```
