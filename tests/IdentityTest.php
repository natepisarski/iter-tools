<?php
namespace IterTools\Tests;

use PHPUnit\Framework\TestCase;
use function IterTools\iter_all;
use function IterTools\iter_count;
use function IterTools\iter_reduce;

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
        $this->assertEquals([1, 2, 3], \IterTools\iter_all([1, 2, 3]));
    }

    /** Tests to see if this counts arrays properly */
    public function testCount()
    {
        $this->assertEquals(4, iter_count([1, 2, 3, 4]));
    }

    /** Tests to see if we can reduce arrays or not. */
    public function testReduce()
    {
        $this->assertEquals(10, iter_reduce([1, 2, 3, 4], fn ($col, $it) => $col + $it), 0);
        // TODO: Test which uses a key from the array
    }
}