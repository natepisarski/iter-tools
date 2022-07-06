# Iter Tools
Use Laravel Collection methods on any kind of data: arrays, generator functions, laravel collections, literally anything.

This package has the exact same API as Laravel Collections.

# Purpose
In a Laravel project, it's not uncommon to try to call `->map()` on an array. Or to call `array_map()` on a Collection.

This package provides 1 consistent interface across all of these different data types. 

# Special Notes
- `null` is, for all intents and purposes, considered an empty Collection for this library. This prevents things like `iter_values($x)` from crashing.

# API
(This will be filled in when the package is 'complete'. See the ROADMAP org mode document for progress)

## `iter_all`
Returns the underlying iterable.

```php
iter_all([1, 2, 3]) // [1, 2, 3] 
```

## `iter_count`
Counts how many items are in this iterable.

```php
iter_count([1, 2, 3, 4, 5]) // 5
```

## `iter_reduce`
Reduces an iterable to a single value. This will pass the result to the next iteration of the function.

```php
iter_reduce([1, 2, 3, 4], fn (int $collection, int $item) => $collection +  $item, 0) // 10
```

## `iter_values`
Returns just the values of the iterable. Effective for re-indexing arrays.

```php
iter_values(['a' => 1, 'b' => 2]) // [1, 2]
```

## `iter_filter`
Keep only the items in the array which satisfy a given predicate. If no predicate is given, falsy items are removed from the list.

Your predicate should be of the form `(value: mixed, key: mixed)`

The array **will not be re-indexed**, so you probably want to combine this with `iter_values`

```php
$array = iter_filter([1, 2, 3, 4, 5, 6, 7], fn (int $x) => $x % 2 ===0); // [1 => 2, 3 => 4, 5 => 6]
iter_values($array); // [2, 4, 6]
```

## `iter_map`
Create a new list by applying a function to each item in this list. The original list will not be modified.

```php
iter_map([1, 2, 3, 4, 5], fn (int $x) => $x + 1) // [2, 3, 4, 5, 6]
```

