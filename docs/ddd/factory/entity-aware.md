# Entity Aware Factory

An entity aware factory is an [object factory](object.md) and additionally bound to
`MsgPhp\Domain\Factory\EntityAwareFactoryInterface`. Its purpose is to factorize entity related objects.

## API

### Extends

- [`DomainObjectFactoryInterface`](object.md)

---

### `reference(string $class, $id): object`

Returns a factorized reference object of type `$class` for an entity known to exist. Supported identity values are
`scalar`, `array` (for composite identifiers), and `object` (i.e. another entity or its [identifier](../identifiers.md)). 

## Implementations

### `MsgPhp\Domain\Factory\EntityAwareFactory`

A generic entity factory.

- `__construct(DomainObjectFactoryInterface $factory, DomainIdentityHelper $identityHelper, array $identifierMapping = [])`
    - `$factory`: The decorated [object factory](object.md)
    - `$identityHelper`: The [identity helper](../identity-helper.md)

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
    ]))
);

// --- USAGE ---

/** @var MyEntity $ref */
$ref = $factory->reference(MyEntity::class, new DomainId('1'));
```

!!! note
    `EntityAwareFactory::reference()` requires [symfony/var-exporter]

### `MsgPhp\Domain\Infra\Doctrine\EntityAwareFactory`

A Doctrine tailored entity aware factory.

- [Read more](../../infrastructure/doctrine-orm.md#entity-aware-factory)

[symfony/var-exporter]: https://packagist.org/packages/symfony/var-exporter
