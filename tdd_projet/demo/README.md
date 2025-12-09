# 🎨 Scheduler TDD - Interface Web de Démonstration

Interface web interactive et moderne pour visualiser et tester le Scheduler en temps réel.

## 🚀 Lancement

Ouvrez simplement le fichier : tdd_projet/demo/index.html` dans votre navigateur.

```bash
# Depuis la racine du projet
cd demo
# Puis ouvrir index.html dans votre navigateur préféré
```

Ou utilisez un serveur local :
```bash
cd demo
python3 -m http.server 8000
# Puis ouvrir http://localhost:8000
```

## ✨ Fonctionnalités

### Gestion des Tâches
- ➕ **Ajouter des tâches** avec nom et périodicité
- 🗑️ **Supprimer des tâches**
- 👁️ **Visualiser toutes les tâches planifiées**
- 📊 **Compteur d'exécutions par tâche**

### Contrôle Temporel
- ⏱️ **Simulation du temps** - avancer minute par minute
- ⏩ **Avance rapide** - sauter 1 heure ou 1 jour
- 🔄 **Reset complet** - tout réinitialiser

### Périodicités Supportées
- ⏰ **Chaque minute** (`*`)
- 🕐 **Toutes les N minutes** (`*/2`, `*/5`, `*/10`)
- 🌅 **Heures fixes** (`0 9 * * *` = 9h tous les jours)
- 📅 **Jours de la semaine** (`0 9 * * 1` = Lundis à 9h)

### Journal d'Exécution
- 📜 **Log en temps réel** des exécutions
- ⏰ **Horodatage** de chaque exécution
- 📈 **Statistiques** (nombre total de tâches et exécutions)

## 🎯 Utilisation

1. **Ajouter une tâche** :
   - Entrez un nom (ex: "Sauvegarde")
   - Choisissez une périodicité
   - Cliquez sur "➕ Ajouter"

2. **Simuler le temps** :
   - Cliquez sur "▶️ Tick" pour avancer d'une minute
   - Utilisez "⏩ +1 heure" pour avancer rapidement
   - Les tâches s'exécutent automatiquement selon leur périodicité

3. **Observer** :
   - Le journal montre chaque exécution
   - Les compteurs se mettent à jour en temps réel
   - L'heure simulée s'affiche en grand

## 🎨 Design

- **Theme** : Dark mode moderne
- **Couleurs** : Gradients dynamiques bleu/violet
- **Animations** : Transitions fluides et micro-interactions
- **Responsive** : S'adapte à toutes les tailles d'écran

## 🔧 Technologies

- **HTML5** : Structure sémantique
- **CSS3** : Animations, gradients, glassmorphism
- **JavaScript ES6+** : Logique applicative
- **Google Fonts** : Typographie Inter

## 💡 Exemples de Scénarios

### Scénario 1 : Tâches quotidiennes
1. Ajoutez "Backup" avec "Tous les jours à 9h"
2. Définissez l'heure à 8h00
3. Cliquez "▶️ Tick" jusqu'à 9h00
4. Observez l'exécution dans le log

### Scénario 2 : Tâches hebdomadaires
1. Ajoutez "Rapport" avec "Lundis à 9h"
2. Vérifiez le jour actuel (affiché en haut)
3. Avancez jusqu'au prochain lundi à 9h
4. La tâche s'exécute !

### Scénario 3 : Multiple tâches
1. Ajoutez 3-4 tâches avec différentes périodicités
2. Utilisez "+1 jour" pour avancer rapidement
3. Observez quelles tâches s'exécutent et quand

## 📸 Captures d'écran

L'interface présente :
- **Header** : Titre animé avec statistiques
- **Panel Gauche** : Gestion des tâches
- **Panel Droit** : Contrôles temps + Journal
- **Design** : Dark theme avec accents bleu/violet

## 🎓 Valeur Pédagogique

Cette interface démontre visuellement :
- ✅ Le fonctionnement du Scheduler
- ✅ Les différentes périodicités
- ✅ L'exécution déterministe
- ✅ La gestion du temps simulé

Parfait pour présenter le projet TDD de manière interactive !

---

**Note** : Cette interface simule le Scheduler en JavaScript côté client. Le code PHP du Scheduler original reste la source de vérité pour les tests et la notation.
