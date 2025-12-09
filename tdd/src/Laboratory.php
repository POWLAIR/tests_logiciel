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

        // First pass: add all products to stock (for circular references)
        foreach ($reactions as $product => $ingredients) {
            $this->stock[$product] = 0.0;
        }

        // Second pass: validate reactions
        foreach ($reactions as $product => $ingredients) {
            if (!is_array($ingredients)) {
                throw new \InvalidArgumentException("Reaction for '{$product}' must be an array");
            }

            foreach ($ingredients as $ingredient) {
                if (!is_array($ingredient) || !isset($ingredient['quantity'], $ingredient['substance'])) {
                    throw new \InvalidArgumentException("Invalid ingredient format in reaction for '{$product}'");
                }

                $substanceName = $ingredient['substance'];
                // Now check if either in base substances OR in reactions (circular case)
                if (!isset($this->stock[$substanceName])) {
                    throw new \InvalidArgumentException("Unknown substance in reaction: {$substanceName}");
                }
            }
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

    /**
     * Make a product with support for circular dependencies.
     * Uses iterative resolution to calculate base ingredients needed.
     *
     * @param string $product The product to make
     * @param float $quantity The desired quantity to produce
     * @return float The actual quantity produced
     */
    public function makeCircular(string $product, float $quantity): float
    {
        // Validate product exists in reactions
        if (!isset($this->reactions[$product])) {
            throw new \InvalidArgumentException("Unknown product: {$product}");
        }

        // Resolve circular dependencies for 1 unit to get base ingredient ratios
        $baseIngredientsPerUnit = $this->resolveCircularDependencies($product, 1.0);
        
        // Calculate maximum producible based on available stock
        $maxProducible = $this->calculateMaxProducible($baseIngredientsPerUnit);
        
        // Limit to requested quantity
        $actualProduced = min($maxProducible, $quantity);
        
        if ($actualProduced <= 0) {
            return 0.0;
        }
        
        // Consume the base ingredients from stock (scaled to actual quantity)
        foreach ($baseIngredientsPerUnit as $substance => $neededPerUnit) {
            if ($neededPerUnit > 0) {
                $totalNeeded = $neededPerUnit * $actualProduced;
                $this->stock[$substance] -= $totalNeeded;
                // Ensure we don't go negative due to floating point errors
                if ($this->stock[$substance] < 0.000001) {
                    $this->stock[$substance] = 0.0;
                }
            }
        }
        
        // Add the produced product to stock
        $this->stock[$product] += $actualProduced;
        
        return $actualProduced;
    }

    /**
     * Resolve circular dependencies iteratively.
     * Expands a product recipe into base ingredients, handling self-references.
     *
     * @param string $product The product name
     * @param float $quantity The quantity needed
     * @return array<string, float> Map of base ingredient => quantity needed
     */
    private function resolveCircularDependencies(string $product, float $quantity): array
    {
        $needs = [$product => $quantity];
        $baseIngredients = [];
        $maxIterations = 1000;
        $iteration = 0;
        $threshold = 0.000001;
        
        while (!empty($needs) && $iteration < $maxIterations) {
            $iteration++;
            $newNeeds = [];
            
            foreach ($needs as $item => $qty) {
                if ($qty <= $threshold) {
                    continue;
                }
                
                // If it's a base substance (no reaction), accumulate it
                if (!isset($this->reactions[$item])) {
                    if (!isset($baseIngredients[$item])) {
                        $baseIngredients[$item] = 0.0;
                    }
                    $baseIngredients[$item] += $qty;
                } else {
                    // Expand the reaction
                    foreach ($this->reactions[$item] as $ingredient) {
                        $qtyPerUnit = $ingredient['quantity'];
                        $substanceName = $ingredient['substance'];
                        $totalNeeded = $qtyPerUnit * $qty;
                        
                        if (!isset($newNeeds[$substanceName])) {
                            $newNeeds[$substanceName] = 0.0;
                        }
                        $newNeeds[$substanceName] += $totalNeeded;
                    }
                }
            }
            
            $needs = $newNeeds;
            
            // Check for convergence
            if ($iteration > 10) {
                $total = array_sum($newNeeds);
                if ($total < $threshold) {
                    break;
                }
            }
        }
        
        // Round to avoid floating point errors
        foreach ($baseIngredients as $name => $qty) {
            $baseIngredients[$name] = round($qty, 6);
        }
        
        return $baseIngredients;
    }

    /**
     * Calculate maximum producible quantity based on base ingredients.
     *
     * @param array<string, float> $baseIngredients Required ingredients
     * @return float Maximum quantity that can be produced
     */
    private function calculateMaxProducible(array $baseIngredients): float
    {
        $max = PHP_FLOAT_MAX;
        
        foreach ($baseIngredients as $substance => $needed) {
            if ($needed <= 0) {
                continue;
            }
            
            $available = $this->stock[$substance] ?? 0.0;
            $ratio = $available / $needed;
            $max = min($max, $ratio);
        }
        
        return $max === PHP_FLOAT_MAX ? 0.0 : $max;
    }
}
