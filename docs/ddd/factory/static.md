# Static factory

A static factory is a utility class, it cannot be initialized as a new instance using `new ...();`. Its purpose is to
factorize a known implementation for a given class.

## Implementations

### `MsgPhp\Domain\Factory\DomainIdFactory`

Factorize an [identifier](../identifiers.md) from any primitive value.

- `static create($value): DomainIdInterface`

#### Basic example

```php
<?php

use MsgPhp\Domain\Factory\DomainIdFactory;
use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Infra\Uuid\DomainId as DomainUuid;

/** @var DomainId $id */
$id = DomainIdFactory::create(1);

/** @var DomainUuid $uuid */
$uuid = DomainIdFactory::create('cf3d2f85-6c86-44d1-8634-af51c91a9a74');
```

### `MsgPhp\Domain\Factory\DomainCollectionFactory`

Factorizes a [collection](../collections.md) from any primitive iterable value.

- `static create(?iterable $value): DomainCollectionInterface`

#### Basic example

```php
<?php

use MsgPhp\Domain\Factory\DomainCollectionFactory;
use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\Infra\Doctrine\DomainCollection as DoctrineDomainCollection;
use Doctrine\Common\Collections\ArrayCollection;

/** @var DomainCollection $collection */
$collection = DomainCollectionFactory::create([1, 2, 3]);

/** @var DoctrineDomainCollection $doctrineCollection */
$doctrineCollection = DomainCollectionFactory::create(new ArrayCollection([1, 2, 3]));
```
