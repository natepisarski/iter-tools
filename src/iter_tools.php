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
    foreach ($iterable as $item) {
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

    foreach ($iterable as $key => $value) {
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
    if (! empty($testForKeyValue) && !(is_int($testOrKey) || is_string($testOrKey))) {
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

    foreach ($iterable as $key => $value) {
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

if (! function_exists('IterTools\pop')) {

  /**
   * "Pops" the last element off of this iterable. This will modify the array in-place, removing the final element
   * from the Array. This will not preserve any keys. If an empty array is given, it will return "null" and not modify
   * the array.
   * @param iterable|null $iterable The iterable, or null. If it's null, it will remain null after the operation.
   * @return mixed
   */
  function iter_pop(?iterable &$iterable): mixed
  {
    $count = iter_count($iterable ?? []);

    if ($count === 0) {
      return null;
    }

    $returnedArray = [];
    $currentCount = -1;

    foreach ($iterable as $value) {
      $currentCount++;

      if ($currentCount === $count - 1) {
        // We are on the final item of the array, so let's just skip this item.
        continue;
      }

      $returnedArray[] = $value;
    }

    $finalItem = $iterable[$count - 1];
    $iterable = $returnedArray;

    return $finalItem;
  }
}