# 🎨 Scheduler TDD - Interface Web de Démonstration

Interface web interactive et moderne pour visualiser et tester le Scheduler en temps réel.

## 🚀 Lancement

Ouvrez simplement le fichier `tdd_projet/demo/index.html` dans votre navigateur.

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
- ✏️ **Modifier des tâches** existantes (bouton éditer)
- 🗑️ **Supprimer des tâches**
- 👁️ **Visualiser toutes les tâches planifiées**
- 📊 **Compteur d'exécutions par tâche**
- 🔄 **Auto-suppression** après exécution (optionnel)

### Types de Tâches

#### 🔁 Tâches Récurrentes
- ⏰ **Chaque minute** (`*`)
- 🕐 **Toutes les N minutes** (`*/2`, `*/5`, `*/10`)
- 🌅 **Heures fixes quotidiennes** (`0 9 * * *` = 9h tous les jours)
- 📅 **Jours de la semaine** (`0 9 * * 1` = Lundis à 9h)
- 📆 **Jours du mois** (`0 9 1 * *` = 1er du mois à 9h, `0 9 15 * *` = 15 du mois)
- ⚙️ **Périodicité personnalisée** : Saisissez votre propre format cron

#### 📅 Tâches One-Time
- Exécution unique à une **date et heure précises**
- Auto-suppression automatique après exécution
- Format : `@YYYY-MM-DD HH:MM`

### Affichage Avancé
- ⏱️ **Prochaine exécution** : Date, heure et countdown relatif
  - "Dans 2j 3h", "Dans 45min", "Imminent"
- ✅ **Badge "Déjà exécutée"** pour tâches terminées
- 🔄 **Badge "Auto-suppression"** pour tâches éphémères

### Calendrier Interactif
- 📆 **Vue mensuelle** avec navigation
- 🎯 **Badges sur jours avec tâches planifiées**
- 📊 **Compteur de tâches par jour**
- 🌟 **Highlight du jour actuel simulé**
- ⏮️⏭️ **Navigation mois précédent/suivant**

### Contrôle Temporel
- ⏱️ **Simulation du temps** - avancer minute par minute
- ⏩ **Avance rapide** - sauter 1 heure ou 1 jour
- 🔄 **Reset complet** - tout réinitialiser

### Interface Utilisateur
- 🎨 **Design moderne** : Dark mode avec gradients dynamiques
- 🌟 **Animations fluides** : Transitions et micro-interactions
- 🔔 **Notifications toast** : Feedback visuel pour chaque action
- 📱 **Responsive** : S'adapte à toutes les tailles d'écran

### Journal d'Exécution
- 📜 **Log en temps réel** des exécutions
- ⏰ **Horodatage** de chaque exécution
- 📈 **Statistiques** (nombre total de tâches et exécutions)

## 🎯 Utilisation

### Ajouter une Tâche Récurrente
1. Entrez un nom (ex: "Sauvegarde")
2. Sélectionnez "Récurrente"
3. Choisissez une périodicité prédéfinie **OU** "Personnalisé" pour saisir votre format
4. Cochez "Auto-supprimer" si souhaité
5. Cliquez sur "➕ Ajouter"

### Ajouter une Tâche One-Time
1. Entrez un nom (ex: "Réunion")
2. Sélectionnez "Date unique"
3. Choisissez la date et l'heure
4. Cliquez sur "➕ Ajouter"
5. La tâche sera **automatiquement supprimée** après exécution

### Modifier une Tâche
1. Cliquez sur "✏️" à côté de la tâche
2. Le formulaire se pré-remplit automatiquement
3. Modifiez la périodicité ou les options
4. Cliquez sur "💾 Mettre à jour" ou "❌ Annuler"

### Simuler le Temps
1. Cliquez sur "▶️ Tick" pour avancer d'une minute
2. Utilisez "⏩ +1 heure" ou "⏭️ +1 jour" pour avancer rapidement
3. Les tâches s'exécutent automatiquement selon leur périodicité
4. Notifications toast pour chaque action

### Observer le Calendrier
1. Le calendrier affiche le mois actuel simulé
2. Les jours avec tâches planifiées ont un **badge coloré**
3. Le jour actuel est **surligné**
4. Naviguez entre les mois avec ◀ et ▶

## 🎨 Design

- **Theme** : Dark mode moderne avec animations
- **Couleurs** : Palette curatée bleu/violet avec accents
- **Gradients** : Dégradés dynamiques pour profondeur
- **Glassmorphism** : Effets transparence sur panels
- **Typographie** : Inter (Google Fonts) pour lisibilité
- **Micro-animations** : Transitions fluides sur interactions

## 🔧 Technologies

- **HTML5** : Structure sémantique
- **CSS3** : Variables CSS, animations, gradients
- **JavaScript ES6+** : Classes, Map, async/await
- **Google Fonts** : Inter
- **Architecture** : MVC pattern, séparation Calendar/Scheduler

## 💡 Exemples de Scénarios

### Scénario 1 : Tâche quotidienne
1. Ajoutez "Backup" avec "Tous les jours à 9h"
2. Définissez l'heure simulée à 8h00
3. Avancez jusqu'à 9h00
4. Observez l'exécution et le compteur

### Scénario 2 : Tâche hebdomadaire
1. Ajoutez "Rapport" avec "Lundis à 9h"
2. Vérifiez le calendrier pour voir les lundis marqués
3. Avancez jusqu'au prochain lundi à 9h
4. La tâche s'exécute !

### Scénario 3 : Tâche one-time
1. Ajoutez "Réunion" en mode "Date unique"
2. Choisissez demain à 14h00
3. Observez le countdown "Dans 1j 6h"
4. Avancez au moment prévu
5. La tâche s'exécute puis est auto-supprimée

### Scénario 4 : Multiple tâches avec calendrier
1. Ajoutez 3-4 tâches avec différentes périodicités
2. Consultez le calendrier : jours marqués
3. Utilisez "+1 jour" pour avancer rapidement
4. Observez quelles tâches s'exécutent et quand

## 📸 Fonctionnalités Visuelles

- **Header** : Titre animé avec statistiques en temps réel
- **Panel Gauche** : Gestion des tâches avec édition inline
- **Panel Droit** : Contrôles temps + Journal d'exécution
- **Calendrier** : Vue mensuelle interactive en bas
- **Toasts** : Notifications en haut à droite
- **Design** : Dark theme premium avec micro-animations

## 🎓 Valeur Pédagogique

Cette interface démontre visuellement :
- ✅ Le fonctionnement du Scheduler backend
- ✅ Les différentes périodicités (cron)
- ✅ L'exécution déterministe
- ✅ La gestion du temps simulé
- ✅ Les concepts TDD en action
- ✅ L'architecture MVC

Parfait pour présenter le projet TDD de manière interactive !

## 🆕 Correspondance Backend ↔ Frontend

| Fonctionnalité Backend | Implémentation Frontend |
|------------------------|-------------------------|
| `scheduleTask()` | Formulaire d'ajout + types de tâches |
| `updateTask()` | Bouton éditer + mode édition |
| `removeTask()` | Bouton supprimer |
| `getTasks()` | Liste des tâches affichée |
| `tick()` | Bouton Tick + avance temps |
| `getNextExecution()` | Affichage "Prochaine exécution" |
| `getExecutionsInRange()` | Calendrier interactif |
| Auto-remove | Checkbox + badge visuel |
| One-time tasks | Type "Date unique" + picker |
| Périodicités cron | Select + input personnalisé |

## 🔮 Améliorations Futures Possibles

- 🎯 Drag & drop pour réorganiser tâches
- 🔍 Filtre par type de périodicité
- 📊 Graphiques d'exécutions
- 💾 Sauvegarde locale (localStorage)
- 🌐 Export/import JSON
- 🎭 Thèmes personnalisables

---

**Note** : Cette interface simule le Scheduler en JavaScript côté client. Le code PHP du Scheduler original (`/src/Scheduler.php`) reste la source de vérité pour les tests PHPUnit et la notation TDD.
