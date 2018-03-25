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

Returns a factorized [identifier](../identifiers.md) for the given entity class from a known primitive value.

---

### `nextIdentifier(string $class): DomainIdInterface`

Returns the nex [identifier](../identifiers.md) for the given entity class. Depending on the implementation its value
might be considered empty if it's not capable to calculate one upfront.

## Implementations

### `MsgPhp\Domain\Factory\EntityAwareFactory`

A generic entity factory. It decorates any object factory and is based on a known [identity mapping](../identity-mapping.md)
as well as a known entity to identifier class mapping.

- `__construct(DomainObjectFactoryInterface $factory, DomainIdentityMappingInterface $identityMapping, array $identifierMapping = [])`
    - `$factory`: The decorated object factory
    - `$identityMapping`: The identity mapping
    - `$identifierMapping`: The identifier class mapping (`['EntityType' => 'IdType']`)

#### Basic example

```php
<?php

use MsgPhp\Domain\DomainId;
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
    new DomainIdentityMapping([
        MyEntity::class => 'id',
    ]),
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
### `MsgPhp\Domain\Infra\Doctrine\EntityAwareFactory`

A Doctrine tailored entity aware factory.

- [Read more](../../infrastructure/doctrine-orm.md#entity-aware-factory)
