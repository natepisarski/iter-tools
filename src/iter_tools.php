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

if (!function_exists('IterTools\all')) {
    function all(?iterable $iterable): iterable
    {
        $finalArray = [];

        foreach ($iterable as $item) {
            array_push($finalArray, $item);
        }

        return $finalArray;
    }
}