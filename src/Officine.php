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
        $nom = strtolower(trim($nom));
        
        // Normaliser les caractères spéciaux (œ -> oe, etc.)
        $nom = str_replace('œ', 'oe', $nom);
        
        // Si c'est déjà un singulier, le retourner
        if (isset($this->mappingPluriel[$nom])) {
            return $nom;
        }
        
        // Chercher si c'est un pluriel
        foreach ($this->mappingPluriel as $singulier => $pluriel) {
            $plurielNormalise = str_replace('œ', 'oe', strtolower($pluriel));
            if ($plurielNormalise === $nom) {
                return $singulier;
            }
        }
        
        return $nom;
    }

    /**
     * Parse une chaîne du type "3 yeux de grenouille" et retourne [quantité, nom]
     */
    private function parser(string $chaine): array
    {
        $chaine = trim($chaine);
        
        // Extraire la quantité et le nom
        if (preg_match('/^(\d+)\s+(.+)$/', $chaine, $matches)) {
            $quantite = (int)$matches[1];
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
     * @return int Quantité en stock
     */
    public function quantite(string $nom): int
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
}
