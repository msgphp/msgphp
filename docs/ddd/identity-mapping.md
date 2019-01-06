# Identity Mapping

The domain identity mapping is a service object and is bound to `MsgPhp\Domain\DomainIdentityMappingInterface`. Its
purpose is to expose identifier metadata for a known domain entity.

## API

### `getIdentifierFieldNames(string $class): array`

Returns the identifier field names for `$class`. Any instance should have an identity composed from these fields.

---

### `getIdentifiers(object $object): array`

Returns the actual, non empty, identifier values of `$object`. Each identifier value is keyed by its corresponding
identifier field name.

## Implementations

### `MsgPhp\Domain\Infra\InMemory\DomainIdentityMapping`

Identity mapping based on a known in-memory mapping.

- `__construct(array $mapping, ObjectFieldAccessor $accessor = null)`
    - `$mapping`: The identity mapping to use
    - `$accessor`: Custom object field accessor. See also [`ObjectFieldAccessor`][api-objectfieldaccessor].

#### Basic Example

```php
<?php

use MsgPhp\Domain\Infra\InMemory\DomainIdentityMapping;

// --- SETUP ---

class MyEntity
{
    public $id;
}

class MyCompositeEntity
{
    public $name;
    public $year;
}

$mapping = new DomainIdentityMapping([
    MyEntity::class => 'id',
    MyCompositeEntity::class => ['name', 'year'],
]);

// --- USAGE ---

$entity = new MyEntity();
$entity->id = ...;

$compositeEntity = new MyCompositeEntity();
$compositeEntity->name = ...;
$compositeEntity->year = ...;

$mapping->getIdentifierFieldNames(MyEntity::class); // ['id']
$mapping->getIdentifierFieldNames(MyCompositeEntity::class); // ['name', 'year']

$mapping->getIdentifiers($entity); // ['id' => ...]
$mapping->getIdentifiers($compositeEntity); // ['name' => ..., 'year' => ...]
```

### `MsgPhp\Domain\Infra\Doctrine\DomainIdentityMapping`

A Doctrine tailored identity mapping.

- [Read more](../infrastructure/doctrine-orm.md#domain-identity-mapping)

[api-objectfieldaccessor]: https://msgphp.github.io/api/MsgPhp/Domain/Infra/InMemory/ObjectFieldAccessor.html
