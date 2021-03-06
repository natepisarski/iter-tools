<?php
namespace IterTools\Tests;

use IterTools\MultipleItemsFoundException;
use PHPUnit\Framework\TestCase;
use function IterTools\iter_all;
use function IterTools\iter_count;
use function IterTools\iter_each;
use function IterTools\iter_empty;
use function IterTools\iter_every;
use function IterTools\iter_filter;
use function IterTools\iter_first;
use function IterTools\iter_get;
use function IterTools\iter_has;
use function IterTools\iter_keys;
use function IterTools\iter_map;
use function IterTools\iter_not_empty;
use function IterTools\iter_pop;
use function IterTools\iter_push;
use function IterTools\iter_reduce;
use function IterTools\iter_skip;
use function IterTools\iter_slice;
use function IterTools\iter_sole;
use function IterTools\iter_some;
use function IterTools\iter_take;
use function IterTools\iter_values;
use function IterTools\iter_when;
use function IterTools\iter_when_empty;
use function IterTools\iter_when_not_empty;
use function IterTools\iter_where;

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
        $this->assertEquals([1, 2, 3], iter_all([1, 2, 3]));
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

    public function testHas()
    {
      $ourArray = [
        'joe' => 14,
        'john' => 2,
        'jim' => 66,
      ];

      $this->assertTrue(iter_has($ourArray, ['joe', 'jim'])); // Fully t rue
      $this->assertFalse(iter_has($ourArray, 'jack')); // Fully false
      $this->assertFalse(iter_has($ourArray, ['joe', 'jack'])); // Partially true
    }

    public function testSole()
    {
      // Scenario 1: Used to return the first item in an array
      $ourArray = [4];
      $this->assertEquals(4, iter_sole($ourArray));

      $ourArray = [1, 2, 3, 4];

      $this->expectException(MultipleItemsFoundException::class);
      $firstValue = iter_sole($ourArray); // This line should throw the exception since more than one item exists.

      // Scenario 2: With a callable function
      $isEven = fn (int $x) => $x % 2 === 0;
      $ourArray = [1, 3, 5, 6, 7, 9];

      $this->assertEquals(6, iter_sole($ourArray, $isEven));
      $ourArray = [...$ourArray, 10];
      $this->expectException(MultipleItemsFoundException::class);
      $firstValue = iter_sole($ourArray, $isEven);

      // Scenario 3: With a key/value pair
      $ourArray = ['a' => 1, 'b' => 2, 'c' => 3];
      $this->assertEquals(2, iter_sole($ourArray, ['b' => 2]));
    }

    public function testEvery()
    {
      $isEven = fn (int $x) => $x % 2 === 0;

      $ourArray = [2, 4, 6, 8];
      $this->assertTrue(iter_every($ourArray, $isEven));

      $ourArray = [2, 3, 4, 6, 8];
      $this->assertFalse(iter_every($ourArray, $isEven));
    }

    public function testEach()
    {
      // Test basic functionality
      $ourArray = [1, 2, 3, 4, 5];
      $pushArray = [];

      $basicPusher = function (int $item) use (&$pushArray) {
        $pushArray = [...$pushArray, $item];
      };

      iter_each($ourArray, $basicPusher);
      $this->assertEquals([1, 2, 3, 4, 5], $pushArray);

      // Test early return
      $advancedPusher = function (int $item) use (&$pushArray) {
        if ($item === 3) {
          return false;
        }

        $pushArray = [...$pushArray, $item];
      };

      $pushArray = [];
      iter_each($ourArray, $advancedPusher);
      $this->assertEquals([1, 2], $pushArray);
    }

    public function testGet()
    {
      $ourArray = [
        'jack' => 2,
        'jim' => 15,
        'joe' => 10,
      ];

      $this->assertEquals(15, iter_get($ourArray, 'jim')); // Happy case
      $this->assertNull(iter_get($ourArray, 'john')); // Not found
      $this->assertEquals(4, iter_get($ourArray, 'john', 4)); // Scalar default values

      $this->assertEquals(10, iter_get($ourArray, 'john', fn () => 10)); // Thunk

      // You can also use get on objects
      $object = (object) ['id' => 4, 'name' => 'Nate'];
      $this->assertEquals(4, iter_get($object, 'id'));
    }

    public function testKeys()
    {
      $ourArray = [
        'jack' => 2,
        'jim' => 15,
        'joe' => 2,
      ];

      $this->assertEquals(['jack', 'jim', 'joe'], iter_keys($ourArray));

      $ourArray = [1, 2, 3, 4];

      $this->assertEquals([0, 1, 2, 3], iter_keys($ourArray));

      $this->assertEquals([], iter_keys(null));
    }

    public function testEmpty()
    {
      $this->assertTrue(iter_empty([]));
      $this->assertTrue(iter_empty(null));
      $this->assertFalse(iter_empty([1]));
    }

    public function testNotEmpty()
    {
      $this->assertFalse(iter_not_empty([]));
      $this->assertFalse(iter_not_empty(null));
      $this->assertTrue(iter_not_empty([1]));
    }

    public function testWhen()
    {
      $ourArray = [1, 2, 3, 4];
      $pusher = fn ($numberToPush) => function (&$collection) use ($numberToPush) {
        $collection = iter_push($collection, $numberToPush);
      };

      iter_when($ourArray, true, $pusher(5));
      $this->assertEquals([1, 2, 3, 4, 5], $ourArray);
      iter_when($ourArray, false, $pusher(6));
      $this->assertEquals([1, 2, 3, 4, 5], $ourArray);
    }

    public function testWhenEmpty()
    {
      $ourArray = [];
      iter_when_empty($ourArray, function (&$collection) {
        $collection = iter_push($collection, 5);
      });

      $this->assertEquals([5], $ourArray);

      iter_when_empty($ourArray, function (&$collection) {
        $collection = iter_push($collection, 6);
      });

      $this->assertEquals([5], $ourArray);
    }

    public function testWhenNotEmpty()
    {
      $ourArray = [1];
      iter_when_not_empty($ourArray, function (&$collection) {
        $collection = iter_push($collection, 5);
      });

      $this->assertEquals([1, 5], $ourArray);

      $ourArray = [];
      iter_when_not_empty($ourArray, function (&$collection) {
        $collection = iter_push($collection, 5);
      }, function (&$collection) {
        $collection = iter_push($collection, 6);
      });

      $this->assertEquals([6], $ourArray);
    }

    public function testFirst()
    {
      $ourArray = [1, 2, 3];
      $this->assertEquals(1, iter_first($ourArray));

      $this->assertNull(iter_first([]));

      $this->assertEquals(2, iter_first($ourArray, fn (int $number) => $number % 2 === 0));
    }

    public function testWhere()
    {
      $ourArray = [
        ['id' => 1, 'name' => 'Joe'],
        ['id' => 2, 'name' => 'Jim'],
        ['id' => 3, 'name' => 'John'],
        ['id' => null, 'name' => 'Nelly'],
      ];

      // Mode 1: Truthiness
      $this->assertEquals([
        ['id' => 1, 'name' => 'Joe'],
        ['id' => 2, 'name' => 'Jim'],
        ['id' => 3, 'name' => 'John'],
      ], iter_where($ourArray, 'id'));

      // Mode 2: Loose Comparison
      $this->assertEquals([
        ['id' => 2, 'name' => 'Jim'],
      ], iter_where($ourArray, 'id', 2));

      // Mode 3: Comparison Operator
      // TODO: Should test all the comparison operators here
      $this->assertEquals([
        ['id' => 2, 'name' => 'Jim'],
        ['id' => 3, 'name' => 'John'],
      ], iter_where($ourArray, 'id', '>', 1));
    }
}