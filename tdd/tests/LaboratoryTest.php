<?php

namespace TDD\Tests;

use PHPUnit\Framework\TestCase;
use TDD\Laboratory;

class LaboratoryTest extends TestCase
{
    /**
     * @test
     * Iteration 1.1 - Constructor with empty substances list
     */
    public function it_can_be_created_with_empty_substances_list(): void
    {
        $laboratory = new Laboratory([]);
        
        $this->assertInstanceOf(Laboratory::class, $laboratory);
    }

    /**
     * @test
     * Iteration 1.2 - Constructor with valid substances and getQuantity
     */
    public function it_initializes_substances_with_zero_quantity(): void
    {
        $laboratory = new Laboratory(['water', 'salt', 'sugar']);
        
        $this->assertSame(0.0, $laboratory->getQuantity('water'));
        $this->assertSame(0.0, $laboratory->getQuantity('salt'));
        $this->assertSame(0.0, $laboratory->getQuantity('sugar'));
    }

    /**
     * @test
     * Iteration 1.3 - Constructor rejects invalid substance names
     */
    public function it_rejects_non_string_substance_names(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All substance names must be strings');
        
        new Laboratory(['water', 123, 'salt']);
    }

    /**
     * @test
     * Iteration 1.3 - Constructor rejects duplicate substances
     */
    public function it_rejects_duplicate_substance_names(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate substance name');
        
        new Laboratory(['water', 'salt', 'water']);
    }

    /**
     * @test
     * Iteration 1.4 - getQuantity with non-existing substance throws exception
     */
    public function it_throws_exception_for_unknown_substance(): void
    {
        $laboratory = new Laboratory(['water', 'salt']);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown substance');
        
        $laboratory->getQuantity('unknown');
    }

    /**
     * @test
     * Iteration 2.1 - Add quantity to existing substance
     */
    public function it_can_add_quantity_to_existing_substance(): void
    {
        $laboratory = new Laboratory(['water', 'salt']);
        
        $laboratory->add('water', 5.5);
        $this->assertSame(5.5, $laboratory->getQuantity('water'));
        
        $laboratory->add('water', 3.0);
        $this->assertSame(8.5, $laboratory->getQuantity('water'));
    }

    /**
     * @test
     * Iteration 2.2 - Add zero quantity
     */
    public function it_can_add_zero_quantity(): void
    {
        $laboratory = new Laboratory(['water']);
        
        $laboratory->add('water', 0.0);
        $this->assertSame(0.0, $laboratory->getQuantity('water'));
    }

    /**
     * @test
     * Iteration 2.3 - Add negative quantity throws exception
     */
    public function it_rejects_negative_quantity(): void
    {
        $laboratory = new Laboratory(['water']);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be non-negative');
        
        $laboratory->add('water', -5.0);
    }

    /**
     * @test
     * Iteration 2.4 - Add to unknown substance throws exception
     */
    public function it_rejects_adding_to_unknown_substance(): void
    {
        $laboratory = new Laboratory(['water']);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown substance');
        
        $laboratory->add('unknown', 5.0);
    }

    /**
     * @test
     * Iteration 3.1 - Constructor with reactions parameter (empty)
     */
    public function it_can_be_created_with_empty_reactions(): void
    {
        $laboratory = new Laboratory(['water', 'salt'], []);
        
        $this->assertInstanceOf(Laboratory::class, $laboratory);
    }

    /**
     * @test
     * Iteration 3.2 - Constructor with valid reactions
     */
    public function it_can_be_created_with_valid_reactions(): void
    {
        $reactions = [
            'saline' => [
                ['quantity' => 2.0, 'substance' => 'water'],
                ['quantity' => 1.0, 'substance' => 'salt']
            ]
        ];
        
        $laboratory = new Laboratory(['water', 'salt'], $reactions);
        
        $this->assertInstanceOf(Laboratory::class, $laboratory);
    }

    /**
     * @test
     * Iteration 3.3 - Constructor validates reaction format
     */
    public function it_validates_reaction_format(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $reactions = [
            'product' => 'invalid'  // Should be an array of ingredients
        ];
        
        new Laboratory(['water'], $reactions);
    }

    /**
     * @test
     * Iteration 3.3 - Constructor validates reaction uses known substances
     */
    public function it_validates_reaction_uses_known_substances(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown substance in reaction');
        
        $reactions = [
            'saline' => [
                ['quantity' => 1.0, 'substance' => 'unknown']
            ]
        ];
        
        new Laboratory(['water'], $reactions);
    }

    /**
     * @test
     * Iteration 3.4 - Add works with products (registered in reactions)
     */
    public function it_can_add_products_directly(): void
    {
        $reactions = [
            'saline' => [
                ['quantity' => 2.0, 'substance' => 'water'],
                ['quantity' => 1.0, 'substance' => 'salt']
            ]
        ];
        
        $laboratory = new Laboratory(['water', 'salt'], $reactions);
        
        $laboratory->add('saline', 10.0);
        $this->assertSame(10.0, $laboratory->getQuantity('saline'));
    }

    /**
     * @test
     * Iteration 4.1 - Make product with sufficient substances
     */
    public function it_can_make_product_with_sufficient_substances(): void
    {
        $reactions = [
            'saline' => [
                ['quantity' => 2.0, 'substance' => 'water'],
                ['quantity' => 1.0, 'substance' => 'salt']
            ]
        ];
        
        $laboratory = new Laboratory(['water', 'salt'], $reactions);
        $laboratory->add('water', 10.0);
        $laboratory->add('salt', 5.0);
        
        $produced = $laboratory->make('saline', 2.0);
        
        $this->assertSame(2.0, $produced);
        $this->assertSame(6.0, $laboratory->getQuantity('water'));  // 10 - (2*2)
        $this->assertSame(3.0, $laboratory->getQuantity('salt'));   // 5 - (1*2)
        $this->assertSame(2.0, $laboratory->getQuantity('saline'));
    }

    /**
     * @test
     * Iteration 4.2 - Make product with insufficient substances
     */
    public function it_makes_partial_product_when_insufficient_substances(): void
    {
        $reactions = [
            'saline' => [
                ['quantity' => 2.0, 'substance' => 'water'],
                ['quantity' => 1.0, 'substance' => 'salt']
            ]
        ];
        
        $laboratory = new Laboratory(['water', 'salt'], $reactions);
        $laboratory->add('water', 5.0);
        $laboratory->add('salt', 2.0);
        
        // Request 3 units, but can only make 2 (limited by salt: 2/1 = 2)
        $produced = $laboratory->make('saline', 3.0);
        
        $this->assertSame(2.0, $produced);
        $this->assertSame(1.0, $laboratory->getQuantity('water'));  // 5 - (2*2)
        $this->assertSame(0.0, $laboratory->getQuantity('salt'));   // 2 - (1*2)
        $this->assertSame(2.0, $laboratory->getQuantity('saline'));
    }

    /**
     * @test
     * Iteration 4.3 - Make with zero stock returns zero
     */
    public function it_returns_zero_when_no_substances_available(): void
    {
        $reactions = [
            'saline' => [
                ['quantity' => 1.0, 'substance' => 'water']
            ]
        ];
        
        $laboratory = new Laboratory(['water'], $reactions);
        
        $produced = $laboratory->make('saline', 5.0);
        
        $this->assertSame(0.0, $produced);
    }

    /**
     * @test
     * Iteration 4.4 - Make unknown product throws exception
     */
    public function it_throws_exception_for_unknown_product(): void
    {
        $laboratory = new Laboratory(['water']);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown product');
        
        $laboratory->make('unknown', 5.0);
    }

    /**
     * @test
     * Iteration 4.5 - Make with products as ingredients
     */
    public function it_can_use_products_as_ingredients(): void
    {
        $reactions = [
            'saline' => [
                ['quantity' => 2.0, 'substance' => 'water'],
                ['quantity' => 1.0, 'substance' => 'salt']
            ],
            'advanced_solution' => [
                ['quantity' => 1.0, 'substance' => 'saline'],
                ['quantity' => 0.5, 'substance' => 'sugar']
            ]
        ];
        
        $laboratory = new Laboratory(['water', 'salt', 'sugar'], $reactions);
        $laboratory->add('saline', 5.0);  // Add pre-made saline
        $laboratory->add('sugar', 3.0);
        
        $produced = $laboratory->make('advanced_solution', 2.0);
        
        $this->assertSame(2.0, $produced);
        $this->assertSame(3.0, $laboratory->getQuantity('saline'));     // 5 - (1*2)
        $this->assertSame(2.0, $laboratory->getQuantity('sugar'));      // 3 - (0.5*2)
        $this->assertSame(2.0, $laboratory->getQuantity('advanced_solution'));
    }
}
