<?php
namespace IterTools\Tests;

use PHPUnit\Framework\TestCase;
use function IterTools\all;

final class IdentityTest extends TestCase
{
    /** Tests to make sure that composer and PHPUnit are properly working. */
    public function testAssertThatTestsRunInTheFirstPlace()
    {
        $this->assertEquals(1, 1);
    }

    /** Tests to make sure that function imports work; and that the all() function is working as intended. */
    public function testAll()
    {
        $this->assertEquals([1, 2, 3], \IterTools\all([1, 2, 3]));
    }
}