<?php

/**
 * Exemple de recettes avec dépendances circulaires
 * Format: A = B + C signifie qu'une unité de A nécessite 1B + 1C
 */

// Exemple simple de l'utilisateur:
// A = B + C
// C = 0.2A + D
// 
// Pour faire 1A il faut:
// - 1B
// - 1C
// Et pour faire 1C il faut:
// - 0.2A
// - 1D
//
// Donc pour faire 1A:
// - 1B
// - 0.2A (qui vient de C)
// - 1D (qui vient de C)
//
// En résolvant: 1A = 1B + (0.2A + 1D)
//               1A - 0.2A = 1B + 1D
//               0.8A = 1B + 1D
//               A = 1.25B + 1.25D

// Pour tester dans Officine, on peut créer:
$recettesCirculaires = [
    // Exemple 1: Simple (comme demandé)
    "potion alpha" => [
        "1 ingredient beta",
        "1 potion gamma"  // gamma dépend de alpha!
    ],
    "potion gamma" => [
        "0.2 potion alpha",  // Dépendance circulaire!
        "1 ingredient delta"
    ],
    
    // Exemple 2: Plus complexe (3 potions interdépendantes)
    "elixir A" => [
        "1 essence pure",
        "0.5 elixir B"
    ],
    "elixir B" => [
        "1 cristal",
        "0.3 elixir C"
    ],
    "elixir C" => [
        "1 poudre",
        "0.1 elixir A"  // Boucle complète!
    ]
];

// Test de résolution manuelle pour validation:
// Pour potion alpha:
// alpha = beta + gamma
// gamma = 0.2*alpha + delta
// 
// Substitution:
// alpha = beta + (0.2*alpha + delta)
// alpha = beta + 0.2*alpha + delta
// alpha - 0.2*alpha = beta + delta
// 0.8*alpha = beta + delta
// alpha = 1.25*beta + 1.25*delta
//
// Donc pour faire 1 potion alpha, il faut:
// - 1.25 ingredient beta
// - 1.25 ingredient delta

?>
