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

    /**
     * @param array<int, string> $substances List of known substance names
     */
    public function __construct(array $substances)
    {
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
}
