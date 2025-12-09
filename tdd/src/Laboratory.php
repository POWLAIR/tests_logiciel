<?php

declare(strict_types=1);

namespace TDD;

/**
 * Laboratory manages substances, reactions and products.
 */
class Laboratory
{
    /** @var array<string, float> */
    private array $stock = [];

    /** @var array<string, array<array{quantity: float, substance: string}>> */
    private array $reactions = [];

    /**
     * @param array<int, string> $substances List of known substance names
     * @param array<string, array<array{quantity: float, substance: string}>> $reactions Product reactions
     */
    public function __construct(array $substances, array $reactions = [])
    {
        // Initialize substances
        $seen = [];
        foreach ($substances as $substance) {
            // Validate substance is a string
            if (!is_string($substance)) {
                throw new \InvalidArgumentException('All substance names must be strings');
            }
            
            // Check for duplicates
            if (isset($seen[$substance])) {
                throw new \InvalidArgumentException("Duplicate substance name: {$substance}");
            }
            
            $seen[$substance] = true;
            $this->stock[$substance] = 0.0;
        }

        // Validate and store reactions
        foreach ($reactions as $product => $ingredients) {
            if (!is_array($ingredients)) {
                throw new \InvalidArgumentException("Reaction for '{$product}' must be an array");
            }

            foreach ($ingredients as $ingredient) {
                if (!is_array($ingredient) || !isset($ingredient['quantity'], $ingredient['substance'])) {
                    throw new \InvalidArgumentException("Invalid ingredient format in reaction for '{$product}'");
                }

                $substanceName = $ingredient['substance'];
                if (!isset($this->stock[$substanceName])) {
                    throw new \InvalidArgumentException("Unknown substance in reaction: {$substanceName}");
                }
            }
            
            // Product is also a valid item in stock
            $this->stock[$product] = 0.0;
        }

        $this->reactions = $reactions;
    }

    /**
     * Get the quantity of a substance in stock.
     */
    public function getQuantity(string $substance): float
    {
        if (!isset($this->stock[$substance])) {
            throw new \InvalidArgumentException("Unknown substance: {$substance}");
        }
        
        return $this->stock[$substance];
    }

    /**
     * Add a quantity of a substance to stock.
     *
     * @param string $substance The substance name
     * @param float $quantity The quantity to add (must be non-negative)
     */
    public function add(string $substance, float $quantity): void
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity must be non-negative');
        }
        
        if (!isset($this->stock[$substance])) {
            throw new \InvalidArgumentException("Unknown substance: {$substance}");
        }
        
        $this->stock[$substance] += $quantity;
    }

    /**
     * Make a product using substances from stock.
     *
     * @param string $product The product to make
     * @param float $quantity The desired quantity to produce
     * @return float The actual quantity produced (may be less if insufficient ingredients)
     */
    public function make(string $product, float $quantity): float
    {
        // Validate product exists in reactions
        if (!isset($this->reactions[$product])) {
            throw new \InvalidArgumentException("Unknown product: {$product}");
        }

        $recipe = $this->reactions[$product];
        
        // Calculate maximum quantity we can actually produce
        $maxProducible = $quantity;
        
        foreach ($recipe as $ingredient) {
            $neededPerUnit = $ingredient['quantity'];
            $substanceName = $ingredient['substance'];
            $available = $this->stock[$substanceName];
            
            if ($neededPerUnit > 0) {
                $maxFromThisIngredient = $available / $neededPerUnit;
                $maxProducible = min($maxProducible, $maxFromThisIngredient);
            }
        }
        
        // If we can't produce anything, return 0
        if ($maxProducible <= 0) {
            return 0.0;
        }
        
        // Consume the ingredients
        foreach ($recipe as $ingredient) {
            $substanceName = $ingredient['substance'];
            $neededTotal = $ingredient['quantity'] * $maxProducible;
            $this->stock[$substanceName] -= $neededTotal;
        }
        
        // Add the produced product to stock
        $this->stock[$product] += $maxProducible;
        
        return $maxProducible;
    }
}
