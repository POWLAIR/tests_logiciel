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
}
