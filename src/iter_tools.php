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
 */

if (!function_exists('IterTools\iter_all')) {

    /**
     * Given any iterable, return it as an array.
     * @param iterable|null $iterable Your iterable, or null.
     * @return iterable The iterable as an array. Or, an empty array for 'null'.
     */
    function iter_all(?iterable $iterable): iterable
    {
        $finalArray = [];

        foreach ($iterable ?? [] as $item) {
            array_push($finalArray, $item);
        }

        return $finalArray;
    }
}

if (!function_exists('IterTools\iter_count')) {

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

if (!function_exists('IterTools\iter_reduce')) {

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