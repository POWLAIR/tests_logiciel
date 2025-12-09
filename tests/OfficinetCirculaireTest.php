<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Officine.php';

/**
 * Tests pour les dépendances circulaires dans Officine
 */
class OfficinetCirculaireTest extends TestCase
{
    private Officine $officine;

    protected function setUp(): void
    {
        $this->officine = new Officine();
    }

    // ========== TESTS DE DÉPENDANCES CIRCULAIRES ==========

    /**
     * @test
     * Test: Résolution simple comme demandé par l'utilisateur
     * A = B + C
     * C = 0.2A + D
     * Donc: A = 1.25B + 1.25D
     */
    public function testRecetteCirculaireSimpleUtilisateur(): void
    {
        // Ajouter les recettes circulaires
        $this->officine->ajouterRecette("potion alpha", [
            "1 ingredient beta",
            "1 potion gamma"
        ]);
        
        $this->officine->ajouterRecette("potion gamma", [
            "0.2 potion alpha",  // Dépendance circulaire!
            "1 ingredient delta"
        ]);
        
        // Rentrer les ingrédients de base
        $this->officine->rentrer("10 ingredient beta");
        $this->officine->rentrer("10 ingredient delta");
        
        // Préparer 1 potion alpha
        $nb = $this->officine->preparerCirculaire("1 potion alpha");
        
        // Devrait préparer 1 potion
        $this->assertEquals(1, $nb);
        
        // Vérifier les stocks consommés
        // Pour 1 alpha: 1.25 beta + 1.25 delta
        $this->assertEqualsWithDelta(8.75, $this->officine->quantite("ingredient beta"), 0.01);
        $this->assertEqualsWithDelta(8.75, $this->officine->quantite("ingredient delta"), 0.01);
        $this->assertEquals(1, $this->officine->quantite("potion alpha"));
    }

    /**
     * @test
     * Test: Préparer plusieurs potions circulaires
     */
    public function testPreparerPlusieursPotionsCirculaires(): void
    {
        $this->officine->ajouterRecette("potion alpha", [
            "1 ingredient beta",
            "1 potion gamma"
        ]);
        
        $this->officine->ajouterRecette("potion gamma", [
            "0.2 potion alpha",
            "1 ingredient delta"
        ]);
        
        // Rentrer assez d'ingrédients pour 5 potions alpha
        // 5 * 1.25 = 6.25 pour chaque
        $this->officine->rentrer("10 ingredient beta");
        $this->officine->rentrer("10 ingredient delta");
        
        $nb = $this->officine->preparerCirculaire("5 potion alpha");
        
        $this->assertEquals(5, $nb);
        $this->assertEqualsWithDelta(3.75, $this->officine->quantite("ingredient beta"), 0.01);
        $this->assertEqualsWithDelta(3.75, $this->officine->quantite("ingredient delta"), 0.01);
    }

    /**
     * @test
     * Test: Stocks insuffisants pour recette circulaire
     */
    public function testRecetteCirculaireStocksInsuffisants(): void
    {
        $this->officine->ajouterRecette("potion alpha", [
            "1 ingredient beta",
            "1 potion gamma"
        ]);
        
        $this->officine->ajouterRecette("potion gamma", [
            "0.2 potion alpha",
            "1 ingredient delta"
        ]);
        
        // Seulement 2 beta (limitant)
        $this->officine->rentrer("2 ingredient beta");
        $this->officine->rentrer("10 ingredient delta");
        
        // On demande 5 mais on ne peut faire que 2/1.25 = 1.6
        $nb = $this->officine->preparerCirculaire("5 potion alpha");
        
        $this->assertLessThanOrEqual(2, $nb);
        $this->assertGreaterThan(1, $nb);
    }

    /**
     * @test
     * Test: Cycle à 3 potions (A->B->C->A)
     */
    public function testRecetteCirculaireTroisPotions(): void
    {
        // A = 1 essence pure + 0.5 B
        $this->officine->ajouterRecette("elixir a", [
            "1 essence pure",
            "0.5 elixir b"
        ]);
        
        // B = 1 cristal + 0.3 C
        $this->officine->ajouterRecette("elixir b", [
            "1 cristal",
            "0.3 elixir c"
        ]);
        
        // C = 1 poudre + 0.1 A (boucle complète!)
        $this->officine->ajouterRecette("elixir c", [
            "1 poudre",
            "0.1 elixir a"
        ]);
        
        // Rentrer les ingrédients de base
        $this->officine->rentrer("20 essence pure");
        $this->officine->rentrer("20 cristal");
        $this->officine->rentrer("20 poudre");
        
        // Tenter de préparer 1 elixir A
        $nb = $this->officine->preparerCirculaire("1 elixir a");
        
        // Devrait réussir à préparer au moins 1
        $this->assertGreaterThan(0, $nb);
        $this->assertLessThanOrEqual(1, $nb);
    }

    /**
     * @test
     * Test: Détection de recette circulaire
     */
    public function testDetectionRecetteCirculaire(): void
    {
        $this->officine->ajouterRecette("potion alpha", [
            "1 ingredient beta",
            "1 potion gamma"
        ]);
        
        $this->officine->ajouterRecette("potion gamma", [
            "0.2 potion alpha",
            "1 ingredient delta"
        ]);
        
        // On peut utiliser la méthode estCirculaire via réflexion
        $reflection = new ReflectionClass($this->officine);
        $method = $reflection->getMethod('estCirculaire');
        $method->setAccessible(true);
        
        $estCirculaire = $method->invoke($this->officine, 'potion alpha');
        
        // Devrait détecter la circularité
        $this->assertTrue($estCirculaire);
    }

    /**
     * @test
     * Test: Recette non-circulaire doit retourner false
     */
    public function testDetectionRecetteNonCirculaire(): void
    {
        // Les recettes normales ne sont pas circulaires
        $reflection = new ReflectionClass($this->officine);
        $method = $reflection->getMethod('estCirculaire');
        $method->setAccessible(true);
        
        $estCirculaire = $method->invoke($this->officine, 'fiole de glaires purulentes');
        
        $this->assertFalse($estCirculaire);
    }
}
