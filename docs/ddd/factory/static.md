# Static Factory

A static factory is a utility class. Its purpose is to ease factorizing some known implementation in a static way.

## Implementations

### `MsgPhp\Domain\Factory\DomainIdFactory`

Factorizes an [domain identifier](../identifiers.md).

- `static create($value): DomainIdInterface`
    - `$value`: Any (primitive) identifier value

#### Basic example

```php
<?php

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Factory\DomainIdFactory;
use MsgPhp\Domain\Infra\Uuid\DomainId as DomainUuid;

// --- USAGE ---

/** @var DomainId $id */
$id = DomainIdFactory::create(1);

/** @var DomainUuid $id */
$id = DomainIdFactory::create('cf3d2f85-6c86-44d1-8634-af51c91a9a74');
```

### `MsgPhp\Domain\Factory\DomainCollectionFactory`

Factorizes a [domain collection](../collections.md).

- `static create(?iterable $value): DomainCollectionInterface`
    - `$value`: Any iterable value or `null` to imply an empty collection
- `static createFromCallable(callable $value): DomainCollectionInterface`
    - `$value`: A callable returning any iterable value

#### Basic Example

```php
<?php

use Doctrine\Common\Collections\ArrayCollection;
use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\Factory\DomainCollectionFactory;
use MsgPhp\Domain\Infra\Doctrine\DomainCollection as DoctrineDomainCollection;

// --- USAGE ---

/** @var DomainCollection $collection */
$collection = DomainCollectionFactory::create([1, 2, 3]);

/** @var DoctrineDomainCollection $collection */
$collection = DomainCollectionFactory::create(new ArrayCollection([1, 2, 3]));
```

#### Rewindable Generator Example

```php
<?php

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\Factory\DomainCollectionFactory;

// --- USAGE ---

/** @var DomainCollection $collection */
$collection = DomainCollectionFactory::createFromCallable(function (): iterable {
    yield 1;
    yield 2;
});

$count = count($collection); // int(2)
$array = iterator_to_array($collection); // [1, 2]
```
