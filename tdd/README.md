# TDD Laboratory Project

Projet de gestion de laboratoire avec substances, réactions et produits.  
Développé en utilisant une approche **TDD stricte** (Test-Driven Development).

## 📋 Objectifs

Créer une classe `Laboratory` capable de :
- 🧪 Gérer un stock de substances
- ⚗️ Définir des réactions (produits créés à partir de substances)
- 🔬 Fabriquer des produits en consommant les substances nécessaires
- ♻️ Gérer des réactions complexes (produits utilisés comme ingrédients)

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
```

## 📐 Méthodologie TDD

Chaque fonctionnalité est implémentée suivant le cycle **Red-Green-Refactor** :

1. 🔴 **RED** : Écrire un test qui échoue
2. 🟢 **GREEN** : Écrire le code minimal pour passer le test
3. 🔵 **REFACTOR** : Améliorer le code sans changer son comportement

Chaque étape fait l'objet d'un commit Git distinct.

## 📚 Structure

```
tdd/
├── src/
│   └── Laboratory.php       # Classe principale
├── tests/
│   └── LaboratoryTest.php   # Tests unitaires
├── composer.json
├── phpunit.xml
└── README.md
```

## 🎯 Étapes de Développement

- [x] Setup du projet
- [ ] Étape 1 : Classe de base avec getQuantity()
- [ ] Étape 2 : Méthode add()
- [ ] Étape 3 : Support des réactions et produits
- [ ] Étape 4 : Méthode make()
