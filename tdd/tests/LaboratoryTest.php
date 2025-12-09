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
}
