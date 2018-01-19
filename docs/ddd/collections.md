# Collections

A domain collection is bound to `MsgPhp\Domain\DomainCollectionInterface`. It's main usage is to aggregate objects bound
together by a root entity.

The technical implementation is generic and may hold any type of elements from any iterable value.

## Implementations

- `MsgPhp\Domain\DomainCollection` (generic collection)
- `MsgPhp\Domain\Infra\Doctrine\DomainCollection` (Doctrine collection)
    - requires: `doctrine/collections`

## API

### `static fromValue(?iterable $value): DomainCollectionInterface`

Factorizes a new collection from a primitive iterable value.

```php
<?php

use MsgPhp\Domain\DomainCollection;

$collection = DomainCollection::fromValue(null); // allowed; empty collection
$collection = new DomainCollection(null); // not allowed
$collection = new DomainCollection([1, 2, 3]); // allowed
```

---

### `isEmpty(): bool`

Tells if a collection is considered empty, i.e. contains zero elements.

```php
<?php

use MsgPhp\Domain\DomainCollection;

$collection = new DomainCollection([]);
$collection->isEmpty(); // true

$collection = new DomainCollection([1, 2, 3]);
$collection->isEmpty(); // false
```

---

### `contains($element): bool`

Tells if a collection contains the given element. Comparison is done strictly.

```php
<?php

use MsgPhp\Domain\DomainCollection;

$collection = new DomainCollection([1, 2, 3]);

$collection->contains(2); // true
$collection->contains('2'); // false
```

---

### `containsKey($key): bool`

Tells if a collection contains an element at the given key/index.

```php
<?php

use MsgPhp\Domain\DomainCollection;

$collection = new DomainCollection([1, 2, 3]);

$collection->containsKey(2); // true
$collection->containsKey(3); // false
```

---

### `first()`

Returns the first element, or `false` if the collection is empty.

```php
<?php

use MsgPhp\Domain\DomainCollection;

$collection = new DomainCollection([1, 2, 3]);
$collection->first(); // int(1)
```

---

### `last()`

Returns the last element, or `false` if the collection is empty.

```php
<?php

use MsgPhp\Domain\DomainCollection;

$collection = new DomainCollection([1, 2, 3]);
$collection->last(); // int(3)
```

---

### `get($key)`

Returns the element at the given key/index, or `null` if the collection is empty.

```php
<?php

use MsgPhp\Domain\DomainCollection;

$collection = new DomainCollection([1, 2, 3]);
$collection->get(1); // int(2)
```

---

### `filter(callable $filter): DomainCollectionInterface`

Returns a **new** collection containing only elements for which `$filter` returns `true`. Keys are preserved.

```php
<?php

use MsgPhp\Domain\DomainCollection;

$collection = new DomainCollection([1, 2, 3]);
$twoOrHigher = $collection->filter(function (int $element): bool {
    return $element >= 2;
});
```

---

### `slice(int $offset, int $limit = 0): DomainCollectionInterface`

Returns a **new** collection containing a slice of elements. By default the slice has no limit, implied by integer `0`. Keys are preserved.

```php
<?php

use MsgPhp\Domain\DomainCollection;

$collection = new DomainCollection([1, 2, 3]);
$onlyTwo = $collection->slice(1, 1);
$twoAndThree = $collection->slice(1);
```

---

### `map(callable $mapper): array`

Returns a map with each collection element as returned by `$mapper`.

```php
<?php

use MsgPhp\Domain\DomainCollection;

$collection = new DomainCollection([1, 2, 3]);
$timesTwo = $collection->map(function (int $element): int {
    return $element * 2;
});
```

## Doctrine example

```php
<?php

use MsgPhp\Domain\Infra\Doctrine\DomainCollection;
use Doctrine\Common\Collections\ArrayCollection;

$collection = DomainCollection::fromValue([1, 2, 3]);
$collectionAlt = DomainCollection::fromValue(new ArrayCollection([1, 2, 3]));
$collectionAlt2 = new DomainCollection(new ArrayCollection([1, 2, 3]));
```

