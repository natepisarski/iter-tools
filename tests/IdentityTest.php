<?php
namespace IterTools\Tests;

use PHPUnit\Framework\TestCase;
use function IterTools\iter_all;
use function IterTools\iter_count;
use function IterTools\iter_filter;
use function IterTools\iter_reduce;
use function IterTools\iter_values;

final class IdentityTest extends TestCase
{
    /** Tests to make sure that composer and PHPUnit are properly working. */
    public function testAssertThatTestsRunInTheFirstPlace(): void
    {
        $this->assertEquals(1, 1);
    }

    /** Tests to make sure that function imports work; and that the all() function is working as intended. */
    public function testAll(): void
    {
        $this->assertEquals([1, 2, 3], \IterTools\iter_all([1, 2, 3]));
    }

    /** Tests to see if this counts arrays properly */
    public function testCount(): void
    {
        $this->assertEquals(4, iter_count([1, 2, 3, 4]));
    }

    /** Tests to see if we can reduce arrays or not. */
    public function testReduce(): void
    {
        $this->assertEquals(10, iter_reduce([1, 2, 3, 4], fn ($col, $it) => $col + $it), 0);
        // TODO: Test which uses a key from the array
    }

    /** Tests iter_values using an array likely to be found in the wild. */
    public function testValues(): void
    {
        $badArray = [
            2 => 'hello',
            3 => 'there',
        ];

        $this->assertEquals(['hello', 'there'], iter_values($badArray));
    }


     /** Tests filtering; making sure we don't clobber the array in the process. */
    public function testFilter(): void
    {
        // TODO: Test which uses a key from the array

        $ourArray = [1, 2, 3, 4, 5, 6];
        $resultArray = iter_values(iter_filter($ourArray, fn ($item) => $item % 2 === 0)); // Get just the event numbers

        $this->assertEquals([2, 4, 6], $resultArray);
        $this->assertNotEquals($ourArray, $resultArray);
    }

}