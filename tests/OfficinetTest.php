<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Officine.php';

/**
 * Tests pour la classe Officine
 */
class OfficinetTest extends TestCase
{
    private Officine $officine;

    protected function setUp(): void
    {
        $this->officine = new Officine();
    }

    // ========== CAS USUELS - rentrer() ==========

    /**
     * @test
     * Test: Entrer un ingrédient dans une officine vide
     */
    public function testRentrerIngredientDansOfficinetVide(): void
    {
        $this->officine->rentrer("3 yeux de grenouille");
        
        $this->assertEquals(3, $this->officine->quantite("œil de grenouille"));
        $this->assertEquals(3, $this->officine->quantite("yeux de grenouille"));
    }

    /**
     * @test
     * Test: Entrer plusieurs fois le même ingrédient
     */
    public function testRentrerMemeIngredientPlusieursVois(): void
    {
        $this->officine->rentrer("3 yeux de grenouille");
        $this->officine->rentrer("2 yeux de grenouille");  // Fixed: use correct plural
        
        $this->assertEquals(5, $this->officine->quantite("œil de grenouille"));
    }

    /**
     * @test
     * Test: Entrer différents types d'ingrédients
     */
    public function testRentrerDifferentsIngredients(): void
    {
        $this->officine->rentrer("5 larmes de brume funèbre");
        $this->officine->rentrer("3 crocs de troll");
        $this->officine->rentrer("10 pincées de poudre de lune");
        
        $this->assertEquals(5, $this->officine->quantite("larme de brume funèbre"));
        $this->assertEquals(3, $this->officine->quantite("croc de troll"));
        $this->assertEquals(10, $this->officine->quantite("pincée de poudre de lune"));
    }

    // ========== CAS USUELS - quantite() ==========

    /**
     * @test
     * Test: Quantité d'un ingrédient inexistant
     */
    public function testQuantiteIngredientInexistant(): void
    {
        $this->assertEquals(0, $this->officine->quantite("œil de grenouille"));
    }

    /**
     * @test
     * Test: Quantité avec singulier et pluriel
     */
    public function testQuantiteSingulierPlurirel(): void
    {
        $this->officine->rentrer("5 larmes de brume funèbre");
        
        $this->assertEquals(5, $this->officine->quantite("larme de brume funèbre"));
        $this->assertEquals(5, $this->officine->quantite("larmes de brume funèbre"));
    }

    // ========== CAS USUELS - preparer() ==========

    /**
     * @test
     * Test: Préparer une potion simple avec stocks suffisants
     */
    public function testPreparerPotionStocksSuffisants(): void
    {
        $this->officine->rentrer("5 larmes de brume funèbre");
        $this->officine->rentrer("3 gouttes de sang de citrouille");
        
        $nbPotions = $this->officine->preparer("2 fioles de glaires purulentes");
        
        $this->assertEquals(2, $nbPotions);
        
        // Vérifier les stocks après préparation
        // Recette: 2 larmes + 1 goutte par potion
        // Pour 2 potions: 4 larmes + 2 gouttes
        $this->assertEquals(1, $this->officine->quantite("larme de brume funèbre")); // 5 - 4
        $this->assertEquals(1, $this->officine->quantite("goutte de sang de citrouille")); // 3 - 2
        $this->assertEquals(2, $this->officine->quantite("fiole de glaires purulentes"));
    }

    /**
     * @test
     * Test: Préparer une potion complexe avec plusieurs ingrédients
     */
    public function testPreparerPotionComplexe(): void
    {
        $this->officine->rentrer("10 crocs de troll");
        $this->officine->rentrer("5 fragments d'écaille de dragonnet");
        $this->officine->rentrer("5 radicelles de racine hurlante");
        
        $nbPotions = $this->officine->preparer("2 soupçons de sels suffocants");
        
        $this->assertEquals(2, $nbPotions);
        
        // Recette: 2 crocs + 1 fragment + 1 radicelle par potion
        $this->assertEquals(6, $this->officine->quantite("croc de troll")); // 10 - 4
        $this->assertEquals(3, $this->officine->quantite("fragment d'écaille de dragonnet")); // 5 - 2
        $this->assertEquals(3, $this->officine->quantite("radicelle de racine hurlante")); // 5 - 2
    }

    /**
     * @test
     * Test: Préparer une potion qui nécessite une autre potion
     */
    public function testPreparerPotionAvecAutrePotion(): void
    {
        // D'abord préparer la fiole de glaires purulentes
        $this->officine->rentrer("10 larmes de brume funèbre");
        $this->officine->rentrer("10 gouttes de sang de citrouille");
        $this->officine->preparer("3 fioles de glaires purulentes");
        
        // Maintenant préparer le baton de pâte sépulcrale
        $this->officine->rentrer("10 radicelles de racine hurlante");
        
        $nbPotions = $this->officine->preparer("1 baton de pâte sépulcrale");
        
        $this->assertEquals(1, $nbPotions);
        $this->assertEquals(7, $this->officine->quantite("radicelle de racine hurlante")); // 10 - 3
        $this->assertEquals(2, $this->officine->quantite("fiole de glaires purulentes")); // 3 - 1
        $this->assertEquals(1, $this->officine->quantite("baton de pâte sépulcrale"));
    }

    // ========== CAS EXTRÊMES ==========

    /**
     * @test
     * Test: Préparer avec stocks insuffisants
     */
    public function testPreparerStocksInsuffisants(): void
    {
        $this->officine->rentrer("3 larmes de brume funèbre");
        $this->officine->rentrer("1 goutte de sang de citrouille");
        
        // Recette demande 2 larmes + 1 goutte par potion
        // On veut 5 potions mais on ne peut en faire qu'1
        $nbPotions = $this->officine->preparer("5 fioles de glaires purulentes");
        
        $this->assertEquals(1, $nbPotions);
        $this->assertEquals(1, $this->officine->quantite("larme de brume funèbre")); // 3 - 2
        $this->assertEquals(0, $this->officine->quantite("goutte de sang de citrouille")); // 1 - 1
    }

    /**
     * @test
     * Test: Préparer avec stocks complètement insuffisants (0 potions)
     */
    public function testPreparerStocksVides(): void
    {
        $nbPotions = $this->officine->preparer("1 fiole de glaires purulentes");
        
        $this->assertEquals(0, $nbPotions);
        $this->assertEquals(0, $this->officine->quantite("fiole de glaires purulentes"));
    }

    /**
     * @test
     * Test: Préparer exactement le nombre de potions possible
     */
    public function testPreparerExactementLaQuantitePossible(): void
    {
        $this->officine->rentrer("4 larmes de brume funèbre");
        $this->officine->rentrer("2 gouttes de sang de citrouille");
        
        // On peut faire exactement 2 potions
        $nbPotions = $this->officine->preparer("2 fioles de glaires purulentes");
        
        $this->assertEquals(2, $nbPotions);
        $this->assertEquals(0, $this->officine->quantite("larme de brume funèbre"));
        $this->assertEquals(0, $this->officine->quantite("goutte de sang de citrouille"));
    }

    /**
     * @test
     * Test: Rentrer 0 quantité
     */
    public function testRentrerQuantiteZero(): void
    {
        $this->officine->rentrer("0 yeux de grenouille");
        
        $this->assertEquals(0, $this->officine->quantite("œil de grenouille"));
    }

    /**
     * @test
     * Test: Stocks très élevés
     */
    public function testStocksTresEleves(): void
    {
        $this->officine->rentrer("1000000 yeux de grenouille");
        
        $this->assertEquals(1000000, $this->officine->quantite("œil de grenouille"));
    }

    /**
     * @test
     * Test: Préparer avec un seul ingrédient manquant
     */
    public function testPreparerUnIngredientManquant(): void
    {
        // Recette soupçon de sels suffocants: 2 crocs + 1 fragment + 1 radicelle
        $this->officine->rentrer("100 crocs de troll");
        $this->officine->rentrer("100 radicelles de racine hurlante");
        // Pas de fragment d'écaille de dragonnet
        
        $nbPotions = $this->officine->preparer("10 soupçons de sels suffocants");
        
        $this->assertEquals(0, $nbPotions);
    }

    // ========== CAS D'ERREUR ==========

    /**
     * @test
     * Test: Format invalide pour rentrer (pas de quantité)
     */
    public function testRentrerFormatInvalideSansQuantite(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->officine->rentrer("yeux de grenouille");
    }

    /**
     * @test
     * Test: Format invalide pour rentrer (chaîne vide)
     */
    public function testRentrerChaineVide(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->officine->rentrer("");
    }

    /**
     * @test
     * Test: Rentrer une quantité négative
     */
    public function testRentrerQuantiteNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->officine->rentrer("-5 yeux de grenouille");
    }

    /**
     * @test
     * Test: Préparer une recette inexistante
     */
    public function testPreparerRecetteInexistante(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->officine->preparer("1 potion miracle");
    }

    /**
     * @test
     * Test: Préparer une quantité négative
     */
    public function testPreparerQuantiteNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->officine->preparer("-1 fiole de glaires purulentes");
    }

    /**
     * @test
     * Test: Préparer 0 potion
     */
    public function testPreparerQuantiteZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->officine->preparer("0 fioles de glaires purulentes");
    }

    /**
     * @test
     * Test: Format invalide pour préparer
     */
    public function testPreparerFormatInvalide(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->officine->preparer("fioles de glaires purulentes");
    }

    // ========== TESTS SUPPLÉMENTAIRES ==========

    /**
     * @test
     * Test: Scénario complet - workflow réaliste
     */
    public function testScenarioComplet(): void
    {
        // Rentrer des ingrédients de base
        $this->officine->rentrer("10 yeux de grenouille");
        $this->officine->rentrer("15 larmes de brume funèbre");
        $this->officine->rentrer("20 pincées de poudre de lune");
        $this->officine->rentrer("5 gouttes de sang de citrouille");
        
        // Préparer quelques potions
        $billes = $this->officine->preparer("3 billes d'âme évanescente");
        $this->assertEquals(3, $billes);
        
        $bouffees = $this->officine->preparer("4 bouffées d'essence de cauchemar");
        $this->assertEquals(4, $bouffees);
        
        // Vérifier les stocks restants
        $this->assertEquals(7, $this->officine->quantite("œil de grenouille")); // 10 - 3
        $this->assertEquals(7, $this->officine->quantite("larme de brume funèbre")); // 15 - 8 (only bouffées use larmes!)
        $this->assertEquals(3, $this->officine->quantite("pincée de poudre de lune")); // 20 - 9 - 8
        $this->assertEquals(5, $this->officine->quantite("goutte de sang de citrouille")); // Non utilisé
        
        // Vérifier les potions créées
        $this->assertEquals(3, $this->officine->quantite("bille d'âme évanescente"));
        $this->assertEquals(4, $this->officine->quantite("bouffée d'essence de cauchemar"));
    }

    /**
     * @test
     * Test: Normalisation des noms avec casse différente
     */
    public function testNormalisationCasse(): void
    {
        $this->officine->rentrer("5 YEUX DE GRENOUILLE");
        $this->officine->rentrer("3 Yeux De Grenouille");
        
        $this->assertEquals(8, $this->officine->quantite("œil de grenouille"));
        $this->assertEquals(8, $this->officine->quantite("YEUX DE GRENOUILLE"));
    }
}
