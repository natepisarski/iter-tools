<?php
namespace IterTools\Tests;

use PHPUnit\Framework\TestCase;
use function IterTools\iter_all;
use function IterTools\iter_count;
use function IterTools\iter_filter;
use function IterTools\iter_map;
use function IterTools\iter_pop;
use function IterTools\iter_push;
use function IterTools\iter_reduce;
use function IterTools\iter_skip;
use function IterTools\iter_slice;
use function IterTools\iter_some;
use function IterTools\iter_take;
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

    /** Tests iter_map to make sure that both value and key/value functions work. */
    public function testMap(): void
    {
      $ourArray = [
        'a' => 1,
        'b' => 2,
        'c' => 3,
      ];

      $resultArray = iter_map($ourArray, fn (int $x) => $x + 1);
      $this->assertEquals([2, 3, 4], $resultArray);

      $resultArray = iter_map($ourArray, fn (int $x, string $key) => "{$key}-{$x}");

      $this->assertEquals(['a-1', 'b-2', 'c-3'], $resultArray);
    }

    /** Tests the contains/some function, making sure all 3 forms of it work. */
    public function testContainsAndSome(): void
    {
      $ourArray = [
        'a' => 1,
        'b' => 2,
        'c' => 3,
      ];

      $contains1 = iter_some($ourArray, 1);
      $contains4 = iter_some($ourArray, 4);

      $containsAEquals1 = iter_some($ourArray, 'a', 1);
      $containsAEquals2 = iter_some($ourArray, 'a', 2);
      $containsKEquals3 = iter_some($ourArray, 'k', 3);

      $containsEven = iter_some($ourArray, fn (int $value) => $value % 2 === 0);
      $containsCKey = iter_some($ourArray, fn (int $value, string $key) => $key === 'c');
      $containsDKey = iter_some($ourArray, fn (int $value, string $key) => $key === 'd');

      $this->assertTrue($contains1);
      $this->assertFalse($contains4);

      $this->assertTrue($containsAEquals1);
      $this->assertFalse($containsAEquals2);
      $this->assertFalse($containsKEquals3);

      $this->assertTrue($containsEven);
      $this->assertTrue($containsCKey);
      $this->assertFalse($containsDKey);
    }

    public function testPush()
    {
      $ourArray = [1, 2, 3];
      $value = iter_push($ourArray, 5);

      $this->assertEquals([1, 2, 3, 5], $value);
    }

    public function testPop()
    {
      $ourArray = [1, 2, 3];
      $lastItem = iter_pop($ourArray);

      $this->assertEquals(3, $lastItem);
      $this->assertEquals([1, 2], $ourArray);

      $ourArray = [];

      $this->assertEquals(null, iter_pop($ourArray));

      $ourArray = null;
      $this->assertEquals(null, iter_pop($ourArray));
      $this->assertEquals(null, $ourArray);
    }

    public function testSkip()
    {
      $ourArray = [1, 2, 3, 4];

      $this->assertEquals([1, 2], iter_take($ourArray, 2), 'Can perform simple skip');
      $this->assertEquals([1, 2, 3, 4], iter_take($ourArray, 5000));

      $ourArray = ['a' => 1, 'b' => 2, 'c' => 3];
      $this->assertEquals([], iter_take($ourArray, 0));
      $this->assertEquals(['a' => 1, 'b' => 2], iter_take($ourArray, 2));
    }

    public function testTake()
    {
      $ourArray = [1, 2, 3, 4, 5];

      $this->assertEquals([3, 4, 5], iter_values(iter_skip($ourArray, 2)));
      $this->assertEquals([], iter_skip($ourArray, 5000));

      $ourArray = ['a' => 1, 'b' => 2, 'c' => 3];
      $this->assertEquals(['a' => 1,  'b' => 2, 'c' => 3], iter_skip($ourArray, 0));
      $this->assertEquals(['c' => 3], iter_skip($ourArray, 2));
    }

    public function testSlice()
    {
      $ourArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
      $this->assertEquals([2 => 3, 3 => 4], iter_slice($ourArray, 2, 2));
    }
}