<?php

/**
 * Classe Officine - Gestion des ingrédients et préparation de potions
 */
class Officine
{
    /**
     * Stock des ingrédients et potions
     * @var array<string, int>
     */
    private array $stocks = [];

    /**
     * Recettes des potions
     * @var array<string, array<string>>
     */
    private array $recettes = [
        "fiole de glaires purulentes" => [
            "2 larmes de brume funèbre",
            "1 goutte de sang de citrouille"
        ],
        "bille d'âme évanescente" => [
            "3 pincées de poudre de lune",
            "1 œil de grenouille"
        ],
        "soupçon de sels suffocants" => [
            "2 crocs de troll",
            "1 fragment d'écaille de dragonnet",
            "1 radicelle de racine hurlante"
        ],
        "baton de pâte sépulcrale" => [
            "3 radicelles de racine hurlante",
            "1 fiole de glaires purulentes"
        ],
        "bouffée d'essence de cauchemar" => [
            "2 pincées de poudre de lune",
            "2 larmes de brume funèbre"
        ]
    ];

    /**
     * Correspondance singulier/pluriel pour les ingrédients
     * @var array<string, string>
     */
    private array $mappingPluriel = [
        "oeil de grenouille" => "yeux de grenouille",
        "larme de brume funèbre" => "larmes de brume funèbre",
        "radicelle de racine hurlante" => "radicelles de racine hurlante",
        "pincée de poudre de lune" => "pincées de poudre de lune",
        "croc de troll" => "crocs de troll",
        "fragment d'écaille de dragonnet" => "fragments d'écaille de dragonnet",
        "goutte de sang de citrouille" => "gouttes de sang de citrouille",
        "fiole de glaires purulentes" => "fioles de glaires purulentes",
        "bille d'âme évanescente" => "billes d'âme évanescente",
        "soupçon de sels suffocants" => "soupçons de sels suffocants",
        "baton de pâte sépulcrale" => "batons de pâte sépulcrale",
        "bouffée d'essence de cauchemar" => "bouffées d'essence de cauchemar"
    ];

    /**
     * Normalise un nom d'ingrédient (singulier/pluriel)
     */
    private function normaliser(string $nom): string
    {
        // Normaliser les espaces multiples en un seul espace
        $nom = preg_replace('/\s+/', ' ', trim($nom));
        $nom = mb_strtolower($nom, 'UTF-8');  // UTF-8 pour gérer correctement les accents
        
        // Normaliser les caractères spéciaux (œ -> oe, etc.)
        $nom = str_replace('œ', 'oe', $nom);
        
        // Si c'est déjà un singulier, le retourner
        if (isset($this->mappingPluriel[$nom])) {
            return $nom;
        }
        
        // Chercher si c'est un pluriel
        foreach ($this->mappingPluriel as $singulier => $pluriel) {
            $plurielNormalise = str_replace('œ', 'oe', mb_strtolower($pluriel, 'UTF-8'));
            if ($plurielNormalise === $nom) {
                return $singulier;
            }
        }
        
        return $nom;
    }

    /**
     * Parse une chaîne du type "3 yeux de grenouille" ou "0.5 yeux de grenouille" et retourne [quantité, nom]
     * Supporte maintenant les quantités fractionnaires pour les recettes circulaires
     */
    private function parser(string $chaine): array
    {
        // Normaliser les espaces multiples en un seul espace
        $chaine = preg_replace('/\s+/', ' ', trim($chaine));
        
        // Extraire la quantité (entière ou fractionnaire) et le nom
        if (preg_match('/^(\d+\.?\d*)\s+(.+)$/', $chaine, $matches)) {
            $quantite = (float)$matches[1];
            $nom = $this->normaliser($matches[2]);
            return [$quantite, $nom];
        }
        
        throw new InvalidArgumentException("Format invalide : '$chaine'. Attendu : 'quantité nom'");
    }

    /**
     * Augmente les stocks d'un ingrédient
     * @param string $chaine Format: "3 yeux de grenouille"
     */
    public function rentrer(string $chaine): void
    {
        [$quantite, $nom] = $this->parser($chaine);
        
        if ($quantite < 0) {
            throw new InvalidArgumentException("La quantité ne peut pas être négative");
        }
        
        if (!isset($this->stocks[$nom])) {
            $this->stocks[$nom] = 0;
        }
        
        $this->stocks[$nom] += $quantite;
    }

    /**
     * Retourne la quantité en stock d'un ingrédient
     * @param string $nom Nom de l'ingrédient (singulier ou pluriel)
     * @return float Quantité en stock (peut être fractionnaire pour support circulaire)
     */
    public function quantite(string $nom): float
    {
        $nom = $this->normaliser($nom);
        return $this->stocks[$nom] ?? 0;
    }

    /**
     * Prépare des potions selon une recette
     * @param string $chaine Format: "2 fioles de glaires purulentes"
     * @return int Nombre de potions réellement préparées
     */
    public function preparer(string $chaine): int
    {
        [$quantiteVoulue, $nomPotion] = $this->parser($chaine);
        
        if ($quantiteVoulue <= 0) {
            throw new InvalidArgumentException("La quantité de potions à préparer doit être positive");
        }
        
        // Vérifier si la recette existe
        if (!isset($this->recettes[$nomPotion])) {
            throw new InvalidArgumentException("Recette inconnue : '$nomPotion'");
        }
        
        $recette = $this->recettes[$nomPotion];
        
        // Calculer le nombre maximum de potions préparables
        $maxPreparable = PHP_INT_MAX;
        $ingredients = [];
        
        foreach ($recette as $ingredient) {
            [$qteNecessaire, $nomIngredient] = $this->parser($ingredient);
            $qteDisponible = $this->quantite($nomIngredient);
            
            // Calculer combien de fois on peut faire la recette avec cet ingrédient
            $nbFois = (int)floor($qteDisponible / $qteNecessaire);
            $maxPreparable = min($maxPreparable, $nbFois);
            
            $ingredients[] = [
                'nom' => $nomIngredient,
                'quantiteParPotion' => $qteNecessaire
            ];
        }
        
        // Le nombre réel de potions préparées
        $nbPotions = min($maxPreparable, $quantiteVoulue);
        
        // Mettre à jour les stocks
        if ($nbPotions > 0) {
            foreach ($ingredients as $ingredient) {
                $this->stocks[$ingredient['nom']] -= $ingredient['quantiteParPotion'] * $nbPotions;
            }
            
            // Ajouter les potions préparées au stock
            if (!isset($this->stocks[$nomPotion])) {
                $this->stocks[$nomPotion] = 0;
            }
            $this->stocks[$nomPotion] += $nbPotions;
        }
        
        return $nbPotions;
    }

    /**
     * Prépare des potions avec support des dépendances circulaires
     * @param string $chaine Format: "2 potions de type A"
     * @return float Nombre de potions réellement préparées (peut être fractionnaire)
     */
    public function preparerCirculaire(string $chaine): float
    {
        [$quantiteVoulue, $nomPotion] = $this->parser($chaine);
        
        if ($quantiteVoulue <= 0) {
            throw new InvalidArgumentException("La quantité de potions à préparer doit être positive");
        }
        
        // Vérifier si la recette existe
        if (!isset($this->recettes[$nomPotion])) {
            throw new InvalidArgumentException("Recette inconnue : '$nomPotion'");
        }
        
        // Résoudre les dépendances (même circulaires) pour 1 unité
        $ingredientsBase = $this->resoudreCirculaire($nomPotion, 1);
        
        // Calculer le maximum préparable (combien de fois on peut faire la recette)
        $maxFois = $this->calculerMaxPreparable($ingredientsBase);
        $nbPotions = min($maxFois, $quantiteVoulue);
        
        if ($nbPotions <= 0) {
            return 0;
        }
        
        // Mettre à jour les stocks - on retire les ingrédients pour nbPotions
        foreach ($ingredientsBase as $ing => $qteParPotion) {
            $qteNecessaire = $qteParPotion * $nbPotions;
            $current = $this->quantite($ing);
            
            $this->stocks[$ing] = max(0, $current - $qteNecessaire);
        }
        
        // Ajouter la potion au stock
        $nomPotion = $this->normaliser($nomPotion);
        if (!isset($this->stocks[$nomPotion])) {
            $this->stocks[$nomPotion] = 0;
        }
        $this->stocks[$nomPotion] += $nbPotions;
        
        return $nbPotions;
    }

    /**
     * Retourne tous les stocks (pour debug/tests)
     */
    public function obtenirStocks(): array
    {
        return $this->stocks;
    }

    /**
     * Réinitialise tous les stocks
     */
    public function vider(): void
    {
        $this->stocks = [];
    }

    /**
     * Ajoute une recette personnalisée (utile pour tester des dépendances circulaires)
     * @param string $nomPotion Nom de la potion
     * @param array $ingredients Liste des ingrédients au format ["quantité nom", ...]
     */
    public function ajouterRecette(string $nomPotion, array $ingredients): void
    {
        $nomPotion = $this->normaliser($nomPotion);
        $this->recettes[$nomPotion] = $ingredients;
        
        // Ajouter aussi au mapping pluriel si nécessaire
        if (!isset($this->mappingPluriel[$nomPotion])) {
            // Créer un pluriel simple (ajouter 's' à la fin)
            $this->mappingPluriel[$nomPotion] = $nomPotion . 's';
        }
    }

    /**
     * Détecte si une recette a des dépendances circulaires
     * @param string $potion Nom de la potion à analyser
     * @param array $visited Potions déjà visitées (pour éviter boucles infinies)
     * @return bool True si circulaire
     */
    private function estCirculaire(string $potion, array $visited = []): bool
    {
        // Si on a déjà visité cette potion, c'est un cycle
        if (in_array($potion, $visited)) {
            return true;
        }
        
        // Si pas de recette, c'est un ingrédient de base
        if (!isset($this->recettes[$potion])) {
            return false;
        }
        
        // Marquer comme visité
        $visited[] = $potion;
        
        // Vérifier chaque ingrédient de la recette
        foreach ($this->recettes[$potion] as $ingredient) {
            [, $nomIngredient] = $this->parser($ingredient);
            
            // Si l'ingrédient a aussi une recette, vérifier récursivement
            if (isset($this->recettes[$nomIngredient])) {
                if ($this->estCirculaire($nomIngredient, $visited)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Construit le graphe de dépendances pour une potion
     * @param string $potion Nom de la potion
     * @return array Graphe de dépendances
     */
    private function construireGrapheDependances(string $potion): array
    {
        $graphe = [];
        $this->ajouterDependances($potion, $graphe);
        return $graphe;
    }

    /**
     * Ajoute récursivement les dépendances au graphe
     */
    private function ajouterDependances(string $potion, array &$graphe): void
    {
        if (isset($graphe[$potion]) || !isset($this->recettes[$potion])) {
            return;
        }
        
        $graphe[$potion] = [];
        
        foreach ($this->recettes[$potion] as $ingredient) {
            [$quantite, $nom] = $this->parser($ingredient);
            $graphe[$potion][$nom] = $quantite;
            
            // Récursif pour les sous-dépendances
            if (isset($this->recettes[$nom])) {
                $this->ajouterDependances($nom, $graphe);
            }
        }
    }

    /**
     * Résout les dépendances circulaires pour calculer les ingrédients de base nécessaires
     * @param string $potion Nom de la potion à préparer
     * @param float $quantite Quantité désirée
     * @return array Tableau [nom_ingredient => quantite_necessaire]
     */
    private function resoudreCirculaire(string $potion, float $quantite): array
    {
        $besoins = [$potion => $quantite];
        $ingredientsBase = [];
        $maxIterations = 1000; // Augmenté pour meilleure convergence
        $iteration = 0;
        $seuilPrecision = 0.000001; // Seuil plus strict
        
        while (!empty($besoins) && $iteration < $maxIterations) {
            $iteration++;
            $nouveauxBesoins = [];
            
            foreach ($besoins as $item => $qte) {
                if ($qte <= $seuilPrecision) {
                    continue;
                }
                
                // Si c'est un ingrédient de base (pas de recette)
                if (!isset($this->recettes[$item])) {
                    if (!isset($ingredientsBase[$item])) {
                        $ingredientsBase[$item] = 0;
                    }
                    $ingredientsBase[$item] += $qte;
                } else {
                    // Décomposer selon la recette
                    foreach ($this->recettes[$item] as $ing) {
                        [$qteParPotion, $nomIng] = $this->parser($ing);
                        $qteNecessaire = $qteParPotion * $qte;
                        
                        if (!isset($nouveauxBesoins[$nomIng])) {
                            $nouveauxBesoins[$nomIng] = 0;
                        }
                        $nouveauxBesoins[$nomIng] += $qteNecessaire;
                    }
                }
            }
            
            $besoins = $nouveauxBesoins;
            
            // Si les besoins sont stables (convergence), arrêter
            if ($iteration > 10) {
                $total = array_sum($nouveauxBesoins);
                if ($total < $seuilPrecision) {
                    break;
                }
            }
        }
        
        // Arrondir pour éviter erreurs de flottants
        foreach ($ingredientsBase as $nom => $qte) {
            $ingredientsBase[$nom] = round($qte, 6);
        }
        
        return $ingredientsBase;
    }

    /**
     * Vérifie si on peut préparer une certaine quantité d'une potion
     * @param array $ingredientsNecessaires Ingrédients nécessaires
     * @return float Quantité maximum préparable (peut être fractionnaire)
     */
    private function calculerMaxPreparable(array $ingredientsNecessaires): float
    {
        $max = PHP_FLOAT_MAX;
        
        foreach ($ingredientsNecessaires as $ing => $qteNecessaire) {
            if ($qteNecessaire <= 0) {
                continue;
            }
            
            $disponible = $this->quantite($ing);
            $ratio = $disponible / $qteNecessaire;
            $max = min($max, $ratio);
        }
        
        return $max === PHP_FLOAT_MAX ? 0 : $max;
    }
}
