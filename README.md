# Laravel Iter Tools
PHP has functions for dealing with arrays (`array_map`, `array_filter`, `array_reduce`, `array_keys`, etc). Meanwhile, Laravel has its own "list" primitive: `Collection`.

This package keeps the API from Laravel's `Collection` intact, while working on:

* `Collection` objects
* `array`'s
* `generator*` functions
* `iterable`'s

# Purpose
In a Laravel project, this will fail:

```php
array_map(fn ($x) => $x, collect())
```

so will this

```php
[]->map(fn ($x) => $x)
```

This can lead to real-world bugs because of PHP's weak dynamic types. For instance, you have no clue if this code will work or crash:

```php
public function do_stuff($list): iterable
{
  $list->map(fn ($x) => echo $x);
}
```

but you **know** this will work:

```php
public function do_stuff($list): iterable
{
  iter_map($list, fn ($x) => echo $x);
}
```

# Special Notes
- `null` is, for all intents and purposes, considered an empty Collection for this library. This prevents things like `iter_values($x)` from crashing.

# API

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

