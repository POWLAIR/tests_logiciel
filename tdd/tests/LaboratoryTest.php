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
}
