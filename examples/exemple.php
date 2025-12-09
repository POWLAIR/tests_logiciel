<?php
/**
 * Exemple d'utilisation de la classe Officine
 * Démontre un workflow complet de gestion d'ingrédients et de préparation de potions
 */

require_once __DIR__ . '/../src/Officine.php';

echo "=== EXEMPLE D'UTILISATION DE LA CLASSE OFFICINE ===\n\n";

// Créer une nouvelle officine
$officine = new Officine();

// === ÉTAPE 1: Rentrer des ingrédients ===
echo "📦 ÉTAPE 1: Rentrer des ingrédients dans l'officine\n";
echo "---------------------------------------------------\n";

$officine->rentrer("10 yeux de grenouille");
$officine->rentrer("15 larmes de brume funèbre");
$officine->rentrer("20 pincées de poudre de lune");
$officine->rentrer("5 gouttes de sang de citrouille");
$officine->rentrer("8 crocs de troll");
$officine->rentrer("3 fragments d'écaille de dragonnet");
$officine->rentrer("12 radicelles de racine hurlante");

echo "✅ Ingrédients ajoutés avec succès!\n\n";

// === ÉTAPE 2: Vérifier les stocks ===
echo "📊 ÉTAPE 2: Vérifier les stocks actuels\n";
echo "---------------------------------------\n";

echo "• Yeux de grenouille: " . $officine->quantite("œil de grenouille") . "\n";
echo "• Larmes de brume funèbre: " . $officine->quantite("larme de brume funèbre") . "\n";
echo "• Pincées de poudre de lune: " . $officine->quantite("pincée de poudre de lune") . "\n";
echo "• Gouttes de sang de citrouille: " . $officine->quantite("goutte de sang de citrouille") . "\n";
echo "• Crocs de troll: " . $officine->quantite("croc de troll") . "\n";
echo "• Fragments d'écaille de dragonnet: " . $officine->quantite("fragment d'écaille de dragonnet") . "\n";
echo "• Radicelles de racine hurlante: " . $officine->quantite("radicelle de racine hurlante") . "\n\n";

// === ÉTAPE 3: Préparer des potions simples ===
echo "⚗️  ÉTAPE 3: Préparer des potions\n";
echo "--------------------------------\n";

// Potion 1: Bille d'âme évanescente
// Recette: 3 pincées de poudre de lune + 1 œil de grenouille
echo "Préparation de 3 billes d'âme évanescente...\n";
$nb = $officine->preparer("3 billes d'âme évanescente");
echo "→ Résultat: $nb potions préparées ✓\n\n";

// Potion 2: Fiole de glaires purulentes
// Recette: 2 larmes de brume funèbre + 1 goutte de sang de citrouille
echo "Préparation de 2 fioles de glaires purulentes...\n";
$nb = $officine->preparer("2 fioles de glaires purulentes");
echo "→ Résultat: $nb potions préparées ✓\n\n";

// Potion 3: Soupçon de sels suffocants
// Recette: 2 crocs de troll + 1 fragment d'écaille de dragonnet + 1 radicelle de racine hurlante
echo "Préparation de 2 soupçons de sels suffocants...\n";
$nb = $officine->preparer("2 soupçons de sels suffocants");
echo "→ Résultat: $nb potions préparées ✓\n\n";

// === ÉTAPE 4: Potion en cascade ===
echo "🔗 ÉTAPE 4: Préparer une potion qui nécessite une autre potion\n";
echo "-------------------------------------------------------------\n";

// Baton de pâte sépulcrale nécessite: 3 radicelles + 1 fiole de glaires purulentes
echo "Préparation d'1 baton de pâte sépulcrale...\n";
echo "(Cette recette nécessite une 'fiole de glaires purulentes' qu'on a préparée!)\n";
$nb = $officine->preparer("1 baton de pâte sépulcrale");
echo "→ Résultat: $nb potion préparée ✓\n\n";

// === ÉTAPE 5: Stocks finaux ===
echo "📊 ÉTAPE 5: Stocks finaux après préparations\n";
echo "-------------------------------------------\n";

echo "🧪 INGRÉDIENTS RESTANTS:\n";
echo "• Yeux de grenouille: " . $officine->quantite("œil de grenouille") . " (10 - 3 = 7)\n";
echo "• Larmes de brume funèbre: " . $officine->quantite("larme de brume funèbre") . " (15 - 4 = 11)\n";
echo "• Pincées de poudre de lune: " . $officine->quantite("pincée de poudre de lune") . " (20 - 9 = 11)\n";
echo "• Gouttes de sang de citrouille: " . $officine->quantite("goutte de sang de citrouille") . " (5 - 2 = 3)\n";
echo "• Crocs de troll: " . $officine->quantite("croc de troll") . " (8 - 4 = 4)\n";
echo "• Fragments d'écaille de dragonnet: " . $officine->quantite("fragment d'écaille de dragonnet") . " (3 - 2 = 1)\n";
echo "• Radicelles de racine hurlante: " . $officine->quantite("radicelle de racine hurlante") . " (12 - 2 - 3 = 7)\n\n";

echo "✨ POTIONS CRÉÉES:\n";
echo "• Billes d'âme évanescente: " . $officine->quantite("bille d'âme évanescente") . "\n";
echo "• Fioles de glaires purulentes: " . $officine->quantite("fiole de glaires purulentes") . " (2 préparées - 1 utilisée)\n";
echo "• Soupçons de sels suffocants: " . $officine->quantite("soupçon de sels suffocants") . "\n";
echo "• Batons de pâte sépulcrale: " . $officine->quantite("baton de pâte sépulcrale") . "\n\n";

// === ÉTAPE 6: Test des cas limites ===
echo "⚠️  ÉTAPE 6: Démonstration des cas limites\n";
echo "-----------------------------------------\n";

// Tentative avec stocks insuffisants
echo "Tentative de préparer 10 bouffées d'essence de cauchemar (stocks insuffisants)...\n";
$nb = $officine->preparer("10 bouffées d'essence de cauchemar");
echo "→ Résultat: $nb potions préparées (maximum possible avec les stocks)\n\n";

// Tentative avec recette inexistante
echo "Tentative de préparer une potion inexistante...\n";
try {
    $officine->preparer("1 potion magique inconnue");
} catch (InvalidArgumentException $e) {
    echo "→ ❌ Erreur capturée: " . $e->getMessage() . "\n\n";
}

// === ÉTAPE 7: Test normalisation ===
echo "🔄 ÉTAPE 7: Démonstration de la normalisation\n";
echo "--------------------------------------------\n";

echo "On peut utiliser singulier OU pluriel, majuscules ou minuscules:\n";
$officine->rentrer("5 YEUX DE GRENOUILLE");  // Majuscules + pluriel
echo "• Ajout de '5 YEUX DE GRENOUILLE'\n";
echo "• Quantité totale (œil de grenouille): " . $officine->quantite("œil de grenouille") . "\n";
echo "• Quantité totale (YEUX DE GRENOUILLE): " . $officine->quantite("YEUX DE GRENOUILLE") . "\n";
echo "• Quantité totale (oeil de grenouille): " . $officine->quantite("oeil de grenouille") . "\n";
echo "→ Toutes les variations donnent le même résultat! ✓\n\n";

echo "=== FIN DE L'EXEMPLE ===\n";
echo "✅ Toutes les opérations ont été exécutées avec succès!\n";
