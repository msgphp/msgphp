# Collections

A domain collection is a [traversable] and bound to `MsgPhp\Domain\DomainCollectionInterface`. Its purpose is to utilize
a primitive iterable value. It may hold any type of element values.

## API

### Extends

- [`\Countable`][countable]
- [`\IteratorAggregate`][iterator-aggregate]

---

### `static fromValue(?iterable $value): DomainCollectionInterface`

Returns a factorized collection from any primitive iterable. Using `null` implies an empty collection.

---

### `isEmpty(): bool`

Tells if a collection is considered empty, i.e. contains zero elements.

---

### `contains($element): bool`

Tells if a collection contains the given element. Comparison is done strictly.

---

### `containsKey($key): bool`

Tells if a collection contains an element at the given key/index.

---

### `first()`

Returns the first element or `false` if the collection is empty.

---

### `last()`

Returns the last element or `false` if the collection is empty.

---

### `get($key)`

Returns the element at the given key/index or `null` if the collection is empty.

---

### `filter(callable $filter): DomainCollectionInterface`

Returns a **new** collection containing only elements for which `$filter` returns `true`. Keys are preserved.

---

### `slice(int $offset, int $limit = 0): DomainCollectionInterface`

Returns a **new** collection containing a slice of elements. By default the slice has no limit, implied by integer `0`.
Keys are preserved.

---

### `map(callable $mapper): array`

Returns a map with each collection element as returned by `$mapper`.

## Pagination API

A collection that is part of a paginated result set is bound to `MsgPhp\Domain\PaginatedDomainCollectionInterface`. Its
purpose is to expose the current pagination.

### Extends

- [`DomainCollectionInterface`](#collections)

---

### `getOffset(): float`

Get the current page offset.

---

### `getLimit(): float`

Get the current page limit (e.g. items per page).

---

### `getCurrentPage(): float`

Get the current page number.

---

### `getLastPage(): float`

Get the last page number.

---

### `getTotalCount(): float`

Get the total no. of items in the full result set.

!!! note
    `count()` should return the no. of items on the current page

## Implementations

### `MsgPhp\Domain\DomainCollection`

A first class citizen domain collection. It leverages `iterable` as underlying data type. Lazy support is built-in for
type `\Traversable`. Meaning the minimal no. of elements are traversed, i.e. until the first element in case of
`isEmpty()`. Note type `\Generator` can only start traversing once.

- `__construct(iterable $elements)`
    - `$elements`: The elements this collection contains

#### Basic example

```php
<?php

use MsgPhp\Domain\DomainCollection;

// --- SETUP ---

$collection = new DomainCollection(['a', 'b', 'c', 1, 2, 3, 'key' => 'value']);

// --- USAGE ---

$collection->isEmpty(); // false
count($collection); // int(7)

$collection->contains(2); // true
$collection->contains('2'); // false

$collection->containsKey(0); // true
$collection->containsKey('0'); // true

$collection->first(); // "a"
$collection->last(); // int(3)

$collection->get('0'); // "a"
$collection->get(3); // int(1)
$collection->get('key'); // "value"

$onlyInts = $collection->filter(function ($value): bool {
    return is_int($value);
});

$firstTwoInts = $onlyInts->slice(0, 2);

$firstTwoIntsPlussed = $firstTwoInts->map(function (int $value): int {
    return ++$value;
}); // [2, 4]
```

### `MsgPhp\Domain\PaginatedDomainCollection`

A first class citizen paginated domain collection to transform any collection into a paginated collection.

- `__construct(iterable $elements, float $offset = .0, float $limit = .0, float $count = null, float $totalCount = null)`
    - `$elements`: The elements this collection contains

### `MsgPhp\Domain\Infra\Doctrine\DomainCollection`

A Doctrine tailored domain collection.

- [Read more](../infrastructure/doctrine-collections.md#domain-collection)

[traversable]: https://secure.php.net/traversable
[countable]: https://secure.php.net/countable
[iterator-aggregate]: https://secure.php.net/iteratoraggregate
