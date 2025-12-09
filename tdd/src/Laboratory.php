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
        foreach ($substances as $substance) {
            $this->stock[$substance] = 0.0;
        }
    }

    /**
     * Get the quantity of a substance in stock.
     */
    public function getQuantity(string $substance): float
    {
        return $this->stock[$substance];
    }
}
