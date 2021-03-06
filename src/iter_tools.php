<?php

namespace IterTools;

/*
 * IterTools is a package which exports utilities for dealing with collections. The motivation behind this package is
 * Laravel's 'Collection' class. It contains a BUNCH of goodies that make interacting with sets of data easy, but there's
 * 1 issue; PHP already has a "set of data" structure, and it's the array.
 *
 * The array functions and the Collection functions are incompatible, and there's not really a good reason for that;
 * PHP comes with the concept of 'iterable', which just means "this thing is a list". So, in theory, if functions could
 * be defined against the 'iterable', rather than 'array' or 'Collection', the functions could be used both on native
 * structures and Laravel collections, and even more.
 *
 * A few more things:
 * - Null is always treated as an empty collection. This means you don't have to do any checks before your data.
 * - For functions which require a specific format, the requirements will be listed with #[ArrayShape] or a documentation comment.
 * - For functions which return an iterable, an array is the concrete format they'll be returned in.
 */

use Exception;

if (! function_exists('IterTools\iter_all')) {

  /**
   * Given any iterable, return it as an array.
   * @param iterable|null $iterable Your iterable, or null.
   * @return array The iterable as an array. Or, an empty array for 'null'.
   */
  function iter_all(?iterable $iterable): array
  {
    // TODO: Should respect keys, not just values.
    $finalArray = [];

    foreach ($iterable ?? [] as $item) {
      array_push($finalArray, $item);
    }

    return $finalArray;
  }
}

if (! function_exists('IterTools\iter_count')) {

  /**
   * Returns the overall number of items in this list.
   * @param iterable|null $iterable Your iterable, or null
   * @return int The number of items in the list. null items still count as items.
   */
  function iter_count(?iterable $iterable): int
  {
    $count = 0;
    foreach ($iterable ?? [] as $item) {
      $count++;
    }

    return $count;
  }
}

if (! function_exists('IterTools\iter_reduce')) {

  /**
   * Reduces an iterable to a single value.
   * @param iterable|null $iterable Your iterable, or null
   * @param callable $reducer Your function. This is a function of the form
   *
   *   ($carry, $item) => <NEW_CARRY> where $carry is the overall value so far, and $item is the current item in the list.
   *
   * The function can also take this form:
   *
   *   ($carry, $item, $key) => <NEW_CARRY>
   *
   * which is functionally the same, but the key of an associative array is passed in the $key variable.
   * @param mixed|null $initialValue The initial value on the first step in reducing. Defaults to 'null'
   * @return mixed The final value after each item has been reduced over.
   */
  function iter_reduce(?iterable $iterable, callable $reducer, mixed $initialValue = null): mixed
  {
    foreach ($iterable ?? [] as $key => $value) {
      $initialValue = $reducer($initialValue, $value, $key);
    }

    return $initialValue;
  }
}

if (! function_exists('IterTools\iter_filter')) {

  /**
   * Filters an iterable. This accepts a predicate that, when it's true, will keep the item in the collection.
   * If there is no predicate given, then anything falsey will be removed from the list.
   *
   * Note, that this will NOT re-index the keys of a non-assocative array. This means that you can wind up with
   * gaps in the key, which can screw up things like JSON encoding. To avoid that, use iter_values
   * @param iterable|null $iterable Your iterable, or null.
   * @param callable|null $predicate The predicate, if any. This takes an item of the list and the key, and returns true or false.
   * @return iterable Returns all the items for which the predicate held true.
   */
  function iter_filter(?iterable $iterable, ?callable $predicate = null): iterable
  {
    $localIterable = $iterable;

    if (is_null($predicate)) {
      $predicate = fn ($value, $key) => $value;
    }

    foreach ($localIterable ?? [] as $key => $value) {
      if (! $predicate($value, $key)) {
        unset($localIterable[$key]);
      }
    }

    return $localIterable;
  }
}

if (! function_exists('IterTools\iter_values')) {

  /**
   * Returns just the values from this array. This is useful to 'reset' the numeric index of an iterable that's been
   * clobbered, or to turn an associative array into
   * @param iterable|null $iterable Your iterable, or null.
   * @return array All of the values in the iterable, as an array.
   */
  function iter_values(?iterable $iterable): array
  {
    $returnedArray = [];

    foreach ($iterable ?? [] as $item) {
      array_push($returnedArray, $item);
    }

    return $returnedArray;
  }
}

if (! function_exists('IterTools\iter_map')) {
  /**
   * Runs this callable method on each item in this iterable. This will not modify the array, and will instead return
   * a new iterable as an array.
   * @param iterable|null $iterable
   * @param callable|null $modifier A function that takes ($value, $key) and returns anything, or even nothing.
   * @return array
   */
  function iter_map(?iterable $iterable, ?callable $modifier = null): array
  {
    $modified = [];

    foreach ($iterable ?? [] as $key => $value) {
      $modified[] = $modifier($value, $key);
    }

    return $modified;
  }
}

if (! function_exists('IterTools\iter_contains')) {
  /**
   * Tests to see if this value is contained inside of this iterable. There are 3 distinct "modes" in which you can use
   * this function:
   *
   * 1) A predicate. You can pass in a "($value, $key) => bool" predicate, and this will return true if one of the
   *    elements returns true.
   * 2) A literal. This will basically work like in_array(), except it will not use a strict comparison.
   * 3) A key/value pair. This is the only time the 3rd argument gets used.
   * @param iterable|null $iterable
   * @param mixed $testOrKey A value of any type, or a string/int if "testForKeyValue" contains the value.
   * @param mixed|null $testForKeyValue A value of any type. Only used when we are looking up a key/value pair.
   * @return bool
   * @throws Exception Throws an exception when the parameters given can't be turned into a sensible operation.
   */
  function iter_contains(?iterable $iterable, mixed $testOrKey, mixed $testForKeyValue = null): bool
  {
    if (! empty($testForKeyValue) && ! (is_int($testOrKey) || is_string($testOrKey))) {
      // If you call us with "testForKeyValue", you have to give us a key. Only ints or strings can be keys.
      $type = get_debug_type($testOrKey);
      throw new Exception("iter_contains must be given an integer or string for the key, $type given.");
    }

    if (! empty($testForKeyValue)) {
      // They want to do a key/value search.
      foreach ($iterable ?? [] as $key => $value) { // TODO: Replace this with a _.get()-like function once implemented
        if ($key === $testOrKey) {
          return $value == $testForKeyValue;
        }
      }

      // Their key wasn't found in the entire array.
      return false; // TODO: Can improve performance here by using array_key_exists if we know this is an array.
    }

    // By this point in the function, we know it's either a simple match or a callback
    $matcher = is_callable($testOrKey) ? $testOrKey : fn (mixed $value) => $value == $testOrKey; // TODO: Can be replace by the "are" helper when/if implemented

    foreach ($iterable ?? [] as $key => $value) {
      if ($matcher($value, $key)) {
        return true;
      }
    }

    return false;
  }
}

if (! function_exists('IterTools\iter_some')) {
  /** Alias for iter_contains
   * @throws Exception
   * @see iter_contains()
   */
  function iter_some(?iterable $iterable, mixed $testOrKey, mixed $testForKeyValue = null): bool
  {
    return iter_contains($iterable, $testOrKey, $testForKeyValue);
  }
}

if (! function_exists('IterTools\iter_push')) {
  /**
   * Pushes an item to the end of the collection.
   * @param iterable|null $iterable The iterable
   * @param mixed $item The item to push to the end. If this is another iterable, it WILL NOT be merged in.
   * @return array
   */
  function iter_push(?iterable $iterable, mixed $item): array
  {
    return [
      ...($iterable ?? []),
      $item,
    ];
  }
}

if (! function_exists('IterTools\iter_pop')) {

  /**
   * "Pops" the last element off of this iterable. This will modify the array in-place, removing the final element
   * from the Array. This will not preserve any keys. If an empty array is given, it will return "null" and not modify
   * the array.
   * @param iterable|null $iterable The iterable, or null. If it's null, it will remain null after the operation.
   * @return mixed
   */
  function iter_pop(?iterable &$iterable): mixed
  {
    [$item, $array] = pure_iter_pop($iterable);
    $iterable = $array;
    return $item;
  }
}

if (! function_exists('IterTools\pure_iter_pop')) {

  /**
   * Performs the same operation as iter_pop, except it won't modify the array in place.
   * It will return a tuple of the array and item.
   * @param iterable|null $iterable
   * @return array Returns a 2-member array, of the form '[$lastValue, $poppedArray]'
   */
  function pure_iter_pop(?iterable $iterable): array
  {
    $count = iter_count($iterable ?? []);

    if ($count === 0) {
      return [null, $iterable];
    }

    $returnedArray = [];
    $currentCount = -1;

    foreach ($iterable ?? [] as $value) {
      $currentCount++;

      if ($currentCount === $count - 1) {
        // We are on the final item of the array, so let's just skip this item.
        continue;
      }

      $returnedArray[] = $value;
    }

    $finalItem = $iterable[$count - 1];

    return [$finalItem, $returnedArray];
  }
}

if (! function_exists('IterTools\iter_skip')) {
  /**
   * Only takes in a certain amount of items from the list.
   * @param iterable|null $iterable
   * @param int $length How many items to accept.
   * @return array
   */
  function iter_take(?iterable $iterable, int $length): iterable
  {
    if ($length < 0) {
      $length = 0;
    }

    if ($length === 0) {
      return [];
    }

    $returnArray = [];

    foreach ($iterable ?? [] as $key => $value) {
      if ($length === 0) {
        return $returnArray;
      }

      $length--;

      $returnArray[$key] = $value;
    }

    return $iterable; // We ran out of members in the iterable before our length, so that means we skip the whole thing.
  }
}

if (! function_exists('IterTools\iter_skip')) {
  /**
   * Skips a certain number of elements on the front of the array. After the skipped elements, the remaining ones are
   * returned as-is.
   * @param iterable|null $iterable Returns an array if we skip anything, or your data type if nothing is skipped.
   * @param int $length How many items to skip.
   * @return iterable
   */
  function iter_skip(?iterable $iterable, int $length): iterable
  {
    if ($length < 0) {
      $length = 0;
    }

    if ($length === 0) {
      return $iterable;
    }

    $returnArray = [];
    foreach ($iterable ?? [] as $key => $value) {
      if ($length <= 0) {
        $returnArray[$key] = $value;
      }

      $length--;
    }

    return $returnArray;
  }
}

if (! function_exists('IterTools\iter_slice')) {
  /**
   * Returns a slice from this iterable. A slice is a sub-section of the iterable.
   * By default, keys are preserved. You can use 'iter_values' to re-index them.
   * @param iterable|null $iterable
   * @param int $start The INDEX to start the slice on
   * @param int|null $length Optional argument you can use to define a max length
   * @return array The slice of the iterable which you asked for.
   */
  function iter_slice(?iterable $iterable, int $start, ?int $length = null): array
  {
    // NOTE: This doubles the time complexity of this function. If you add a backdoor in iter_take where a negative
    // value returns the entire list, you can make this twice as efficient.
    $length ??= iter_count($iterable);
    return iter_take(iter_skip($iterable, $start), $length);
  }
}

if (! function_exists('IterTools\iter_has')) {

  /**
   * Test to see if an iterable has a key / keys.
   * @param iterable|null $iterable
   * @param mixed $key A key, or an iterable of keys to check for. If this is an iterable, ALL keys must be present.
   * @return bool True if all keys are found, false otherwise.
   */
  function iter_has(?iterable $iterable, mixed $key): bool
  {
    if (! is_iterable($key)) {
      $key = [$key];
    }

    // TODO: Definitely a more efficient algorithm to use here
    foreach ($key as $desiredKey) {
      foreach ($iterable ?? [] as $listKey => $listValue) {
        if ($listKey === $desiredKey) {
          continue 2;
        }
      }
      // If we made it here, it means a key wasn't found in the list
      return false;
    }

    return true;
  }
}

if (! function_exists('IterTools\iter_sole')) {

  /**
   * Returns the sole item of an iterable. This can be used in 3 distinct ways.
   *
   * 1) With no argument, we return the first element of the list (if there is ONLY ONE ELEMENT). If there is more than
   *    one element, then an MultipleItemsFoundException is thrown. If there are no items, an ItemNotFoundException is thrown.
   * 2) With a callable function  we return the first element to return true on this element. The function can take
   *    ($value, $key). If more than one item is found, MultipleItemsFoundException is thrown. ItemNotFoundException is returnd
   *    if there are no valid items.
   * 3) With a key/value pair (array, Collection, etc), the first element to match exactly is returned. If there are none,
   *    the ItemNotFoundException is thrown again. This can't have a MultipleItemsFoundException since each array must
   *    contain unique keys.
   * @param iterable|null $iterable
   * @param callable|iterable|null $argument
   * @return mixed
   * @throws ItemNotFoundException
   * @throws MultipleItemsFoundException
   */
  function iter_sole(?iterable $iterable, callable|iterable|null $argument = null): mixed
  {
    $iterable ??= [];

    if (is_null($argument)) {
      // Mode 1: List Mode
      $count = iter_count($iterable);

      if ($count === 0) {
        throw new ItemNotFoundException;
      }
      if ($count > 1) {
        throw new MultipleItemsFoundException;
      }

      return $iterable[0];
    }

    if (is_callable($argument)) {
      $foundItem = null;
      $wasItemFound = false; // We have to track this separately because null may be the item we're looking for.
      foreach ($iterable ?? [] as $key => $value) {
        if ($argument($value, $key)) {
          if (($wasItemFound)) {
            // An item was already found
            throw new MultipleItemsFoundException;
          }

          $foundItem = $value;
          $wasItemFound = true;
        }
      }

      if (! $wasItemFound) {
        throw new ItemNotFoundException;
      }
      return $foundItem;
    }

    // At this point, we just hope that 'argument' was a key/value pair. If not, this line will throw a PHP Error.
    if (iter_count($iterable) > 1) {
      // They have passed in more than 1 key/value pairs. We can't do anything with this
      throw new ItemNotFoundException;
    }

    if (! iter_has($iterable, $argument)) {
      throw new ItemNotFoundException;
    }

    foreach ($argument as $key => $value) {
      // TODO: There must be a less jank way of doing this, probably with a new function. This loop's only purpose
      // is to extract the key
      $listValue = $iterable[$key];
      if ($listValue !== $value) {
        throw new ItemNotFoundException;
      }

      return $listValue;
    }

    // If we get to this point in the code, it means that the argument they gave us was not in one of the three known forms.
    throw new ItemNotFoundException;
  }
}

if (! function_exists('IterTools\iter_every')) {
  /**
   * Determine if the predicate holds true for each item in the list. If the list is empty or null, we return true.
   * @param iterable|null $iterable
   * @param callable $predicate A predicate, which takes (value, key) => Boolean
   * @return bool True if every item in the list passes, false otherwise.
   */
  function iter_every(?iterable $iterable, callable $predicate): bool
  {
    foreach ($iterable ?? [] as $key => $item) {
      if (! $predicate($item, $key)) {
        return false;
      }
    }

    return true;
  }
}

if (! function_exists('IterTools\iter_each')) {
  /**
   * Passes each element of the list to the given function. You can have your function return 'false' to stop execution
   * of the function.
   * @param iterable|null $iterable
   * @param callable $function A function which takes (value, key) => void|bool (false to stop execution)
   * @return iterable|null Returns whatever was passed in as the iterable.
   */
  function iter_each(?iterable $iterable, callable $function): ?iterable
  {
    foreach ($iterable ?? [] as $key => $value) {
      if ($function($value, $key) === false) {
        break;
      }
    }

    return $iterable;
  }
}

if (! function_exists('IterTools\iter_get')) {
  /**
   * Gets the item at this key. If the key isn't found, the default value is returned - which is null by default.
   * If your default value is callable, it's executed with no arguments. This way you can pass in a "thunk" to have its
   * value returned.
   * @param iterable|object|null $iterable |null $iterable
   * @param string|int $key
   * @param mixed|null $defaultValue
   * @return mixed
   */
  function iter_get(iterable|object|null $iterable, string|int $key, mixed $defaultValue = null): mixed
  {
    $getDefault = fn () => is_callable($defaultValue) ? $defaultValue() : $defaultValue;

    if (empty($iterable)) {
      return $getDefault();
    }

    if (is_object($iterable) && !is_iterable($iterable)) {
      return $iterable->$key ?? $getDefault();
    }

    // TODO: Should support objects as well

    // TODO: Can probably be significantly more performant
    foreach ($iterable as $iterableKey => $value) {
      if ($key === $iterableKey) {
        return $value;
      }
    }

    return $getDefault();
  }
}

if (! function_exists('IterTools\iter_keys')) {
  /**
   * Returns an array of all root-level keys this iterable has.
   * @param iterable|null $iterable
   * @return array
   */
  function iter_keys(?iterable $iterable): array
  {
    return iter_map($iterable, fn ($value, $key) => $key);
  }
}

if (! function_exists('IterTools\iter_empty')) {
  /**
   * Determine if this iterable is empty (i.e null, or containing no items).
   * @param iterable|null $iterable
   * @return bool
   */
 function iter_empty(?iterable $iterable): bool
 {
   foreach ($iterable ?? [] as $value) {
     return false;
   }

   return true;
 }
}

if (! function_exists('IterTools\iter_is_empty')) {
  /**
   * Alias for iter_empty which matches Laravel's name.
   * @param iterable|null $iterable
   * @return bool
   */
  function iter_is_empty(?iterable $iterable): bool
  {
    return iter_empty($iterable);
  }
}

if (! function_exists('IterTools\iter_not_empty')) {
  /**
   * Negation of iter_empty. This will return true if any item exists in the list.
   * @param iterable|null $iterable
   * @return bool
   */
  function iter_not_empty(?iterable $iterable): bool
  {
    return ! iter_empty($iterable);
  }
}

if (! function_exists('IterTools\iter_is_not_empty')) {
  /**
   * Alias for iter_not_empty that matches Laravel's name.
   * @param iterable|null $iterable
   * @return bool
   */
  function iter_is_not_empty(?iterable $iterable): bool
  {
    return iter_not_empty($iterable);
  }
}

if (! function_exists('IterTools\iter_when')) {
  /**
   * When your condition evaluates to true, run a specific callback. You may also pass in another callback for when it
   * evaluates to falsy.
   * @param iterable|null $iterable
   * @param mixed $truthyOrFalsy Something that can be tested as a boolean value. Likely to be some expression in client code.
   * @param callable $onTruthy A callback to execute when it's truthy. This takes (?iterable, truthyOrFalsy) where iterable is a reference.
   * @param callable|null $onFalsy A callback to execute when it's falsy. This takes (?iterable, truthyOrFalsy) where iterable is a reference
   * @return iterable|null Returns the Collection we were given. The return value will not likely be used.
   */
  function iter_when(?iterable &$iterable, mixed $truthyOrFalsy, callable $onTruthy, ?callable $onFalsy = null): ?iterable
  {
    if ($truthyOrFalsy) {
      $onTruthy($iterable, $truthyOrFalsy);
    } else if ($onFalsy) {
      $onFalsy($iterable, $truthyOrFalsy);
    }

    return $iterable;
  }
}

if (! function_exists('IterTools\iter_when_empty')) {
  /**
   * When your iterable is empty or null, run a specific callback. You may pass in another callback for when it is not
   * empty.
   * @param iterable|null $iterable
   * @param callable $onTruthy A callback to execute when it's empty. This takes (?iterable) as a reference.
   * @param callable|null $onFalsy An optional callback to execute when it's not empty. This takes (?iterable) as a reference.
   * @return iterable|null
   */
  function iter_when_empty(?iterable &$iterable, callable $onTruthy, ?callable $onFalsy = null): ?iterable
  {
    return iter_when(
      $iterable,
      iter_empty($iterable),
      $onTruthy,
      $onFalsy,
    );
  }
}

if (! function_exists('IterTools\iter_when_not_empty')) {
  /**
   * When your iterable is NOT empty or null, run a specific callback. You may pass in another callback for when it IS
   * empty. This is the inverse of iter_when_empty
   * @param iterable|null $iterable
   * @param callable $onTruthy The callback to run when it's not empty. This takes (?iterable) as a reference.
   * @param callable|null $onFalsy An optional callback to execute when it's not empty.
   * @return iterable|null
   */
  function iter_when_not_empty(?iterable  &$iterable, callable $onTruthy, ?callable $onFalsy = null): ?iterable
  {
    return iter_when(
      $iterable,
      ! iter_empty($iterable),
      $onTruthy,
      $onFalsy,
    );
  }
}

if (! function_exists('IterTools\iter_first')) {
  /**
   * Returns the first item in the iterable. If you pass in the predicate, this will return the value for the first
   * item it holds true for. The form of the predicate is (value: T, key: mixed) => bool
   *
   * If the list is empty, or none held true for the predicate, then null is returned.
   * @param iterable|null $iterable
   * @param callable|null $predicate
   * @return mixed
   */
  function iter_first(?iterable $iterable, ?callable $predicate = null): mixed
  {
    foreach ($iterable ?? [] as $key => $value) {
      if (! $predicate) {
        return $value;
      }

      if ($predicate($value, $key)) {
        return $value;
      }
    }

    return null;
  }
}

if (! function_exists('IterTools\iter_where')) {
  /**
   * Returns any items in the collection that meet the criteria of your test. You can call this function in 3 separate ways:
   *
   * 1) Truthiness Test
         iter_where([ ['id' => 1], ['id' => 2], ['id' => 0] ], 'id') === [['id' => 1], ['id' => 2]]
   *
   * 2) Implicit Loose Equality Check
         iter_where([ ['id' => 1], ['id' => 2], ['id' => 0] ], 'id', '2') === [['id' => 2]]
   *
   * 3) Comparison Operator. For all possible values, @see ComparisonOperator.
         iter_where([ ['id' => 1], ['id' => 2], ['id' => 0] ], 'id', '>', '0') === [['id' => 1], ['id' => 2]]
   * @param iterable|null $iterable
   * @param mixed $key The key to look for in the sub-arrays, or objects.
   * @param mixed $comparisonOperatorOrValue
   * @param mixed|null $value
   * @param bool $useStrictComparisonsByDefault Whether or not to compare values using strict comparisons unless otherwise stated.
   * @return array
   * @throws UnrecognizedComparisonOperatorException
   */
  function iter_where(
    ?iterable $iterable,
    mixed $key,
    mixed $comparisonOperatorOrValue = null,
    mixed $value = null,
    bool $useStrictComparisonsByDefault = false,
  ): array
  {
    $equalityComparator = fn ($x, $y) => $useStrictComparisonsByDefault ? $x === $y : $x == $y;

    // There are 3 distinct modes here: truthiness check, loose equality check, and comparison operator check.

    if (empty($comparisonOperatorOrValue) && empty($value)) {
      // TODO: Should this automatically call iter_values?
      // Mode 1: Truthiness Check - only items who have a truthy key will be kept.
      return iter_values(iter_filter($iterable, fn ($item) => iter_get($item, $key)));
    }

    if (empty($value)) {
      // Mode 2: Implicit Comparison
      return iter_values(
        iter_filter($iterable, fn ($item) => $equalityComparator(iter_get($item, $key), $comparisonOperatorOrValue))
      );
    }

    // Mode 3: Explicit comparison operator
    $returnedArray = [];
    foreach ($iterable ?? [] as $item) {
      $thisValue = iter_get($item, $key);
      $comparison = ComparisonOperator::tryFrom($comparisonOperatorOrValue);

      if (empty($comparison)) {
        // We don't recognize the comparison operator that was used.
        throw new UnrecognizedComparisonOperatorException("Comparison Operator '$comparisonOperatorOrValue' was not recognized as a valid operation");
      }

      $passed = match ($comparison) {
        ComparisonOperator::Equals => $equalityComparator($thisValue, $value),
        ComparisonOperator::LooseEquals => $thisValue == $value,
        ComparisonOperator::LooseNotEquals => $thisValue != $value,
        ComparisonOperator::StrictEquals => $thisValue === $value,
        ComparisonOperator::StrictNotEquals => $thisValue !== $value,
        ComparisonOperator::GreaterThan => $thisValue > $value,
        ComparisonOperator::LessThan => $thisValue < $value,
        ComparisonOperator::GreaterThanOrEqualTo => $thisValue >= $value,
        ComparisonOperator::LessThanOrEqualTo => $thisValue <= $value,
        ComparisonOperator::In => iter_some($value, $thisValue),
        ComparisonOperator::NotIn => ! iter_some($value, $thisValue),
      };

      if ($passed) {
        $returnedArray[] = $item;
      }
    }

    return $returnedArray;
  }
}

if (! function_exists('IterTools\iter_where_strict')) {
  /**
   * Uses the same signature as iter_where, except it will use strict comparisons by default. You can still use a loose
   * comparison by passing in '==' as the comparison operator.
   * @param iterable|null $iterable
   * @param mixed $key The key to look for in the sub-arrays / objects
   * @param mixed|null $comparisonOperatorOrValue See ComparisonOperator for a full list of options
   * @param mixed|null $value The value to search for if using a comparison operator.
   * @return array
   * @throws UnrecognizedComparisonOperatorException
   */
  function iter_where_strict(?iterable $iterable, mixed $key, mixed $comparisonOperatorOrValue = null, mixed $value = null): array
  {
    return iter_where($iterable, $key, $comparisonOperatorOrValue, $value, useStrictComparisonsByDefault: true);
  }
}