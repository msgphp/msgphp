# Entity Aware Factory

An entity aware factory is an [object factory](object.md) and additionally bound to
`MsgPhp\Domain\Factory\EntityAwareFactoryInterface`. Its purpose is to factorize entity related objects.

## API

### Extends

- [`DomainObjectFactoryInterface`](object.md)

---

### `reference(string $class, $id): object`

Returns a factorized reference object for a known existing entity object. The object must be of type `$class`. Any type
of [identity](../identities.md) value can be passed as `$id`.

---

### `identify(string $class, $value): DomainIdInterface`

Returns a factorized [domain identifier](../identifiers.md) for the given entity class from a known primitive value.

---

### `nextIdentifier(string $class): DomainIdInterface`

Returns the next [domain identifier](../identifiers.md) for the given entity class. Depending on the implementation its
value might be considered empty if it's not capable to calculate one upfront.

## Implementations

### `MsgPhp\Domain\Factory\EntityAwareFactory`

A generic entity factory.

- `__construct(DomainObjectFactoryInterface $factory, DomainIdentityHelper $identityHelper, array $identifierMapping = [])`
    - `$factory`: The decorated [object factory](object.md)
    - `$identityHelper`: The [identity helper](../identities.md)
    - `$identifierMapping`: The identifier class mapping (`['EntityType' => 'IdType']`)

#### Basic example

```php
<?php

use MsgPhp\Domain\{DomainId, DomainIdentityHelper};
use MsgPhp\Domain\Factory\{DomainObjectFactory, EntityAwareFactory};
use MsgPhp\Domain\Infra\InMemory\DomainIdentityMapping;

// --- SETUP ---

class MyEntity
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}

$factory = new EntityAwareFactory(
    new DomainObjectFactory(),
    new DomainIdentityHelper(new DomainIdentityMapping([
        MyEntity::class => 'id',
    ])),
    [
        MyEntity::class => DomainId::class,
    ]
);

// --- USAGE ---

/** @var MyEntity $ref */
$ref = $factory->reference(MyEntity::class, new DomainId('1'));

/** @var DomainId $id */
$id = $factory->identify(MyEntity::class, 1);

/** @var DomainId $id */
$id = $factory->nextIdentifier(MyEntity::class);
```

!!! note
    `EntityAwareFactory::reference()` requires [symfony/var-exporter]

### `MsgPhp\Domain\Infra\Doctrine\EntityAwareFactory`

A Doctrine tailored entity aware factory.

- [Read more](../../infrastructure/doctrine-orm.md#entity-aware-factory)

[symfony/var-exporter]: https://packagist.org/packages/symfony/var-exporter
