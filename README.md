# Iter Tools
Use Laravel Collection methods on any kind of data: arrays, generator functions, laravel collections, literally anything.

This package maintains the exact same API that laravel's Collection class uses, except the first argument to
each method is the Collection (since we're not using methods).

# Purpose
In a Laravel project, it's not uncommon to try to call `->map()` on an array. Or to call `array_map()` on a Collection.

This package provides 1 consistent interface across all of these different data types. 

# Special Notes
- `null` is, for all intents and purposes, considered an empty Collection for this library. This prevents things like `iter_values($x)` from crashing.

# API
(This will be filled in when the package is 'complete'. See the ROADMAP org mode document for progress)
