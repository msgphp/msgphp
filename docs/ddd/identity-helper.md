# Identity Helper

`MsgPhp\Domain\DomainIdentityHelper` is a utility domain service. Its purpose is to ease working with domain identities
and the [identity mapping](identity-mapping.md).

A domain identity is a composite value (`array`) holding one or more individual identifier values, indexed by an
identifier field name. Its purpose is to uniquely identify a domain entity.

Identifier values can be of any type; a [domain identifier](identifiers.md), another (foreign) domain entity, or any
primitive scalar.

A single identifier value might represent an identity in case the identity is not a composite.

## API

### `isIdentifier($value): bool`

Tells if `$value` is a known identifier value. This is either a [domain identifier](identifiers.md) object, or another
domain entity.

---

### `isEmptyIdentifier($value): bool`

Tells if `$value` is a known empty identifier value. It returns `true` if the specified value is either `null`, an 
_empty_ [domain identifier](identifiers.md), or another domain entity without its identity set.

---

### `normalizeIdentifier($value)`

Returns the primitive identifier value of `$value`. An empty identifier (see `isEmptyIdentifier()`) is normalized to
`null`, a [domain identifier](identifiers.md) to `string`, and another domain entity to its normalized identity (`scalar|array`).
Any other type of value is preserved.

---

### `getIdentifierFieldNames(string $class): array`

See `DomainIdentityMappingInterface::getIdentifierFieldNames()`.

---

### `getIdentifiers(object $object): array`

See `DomainIdentityMappingInterface::getIdentifiers()`.

---

### `isIdentity(string $class, $value): bool`

Tells if `$value` is a valid identity for type `$class`. An identity is considered valid if the entity class uses
a single identifier value as identity and `$value` is a non empty identifier (see `isEmptyIdentifier()`).

In case of one or more identifier values, given in the form of an array, its keys must exactly match the available
identifier field names and its values must contain no empty identifiers.

---

### `toIdentity(string $class, $value): array`

Returns a composite identity value for `$class` from `$value`.

## Basic Example

```php
<?php

use MsgPhp\Domain\{DomainId, DomainIdentityHelper};
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

$helper = new DomainIdentityHelper(new DomainIdentityMapping([
   MyEntity::class => 'id',
   MyCompositeEntity::class => ['name', 'year'],
]));

// --- USAGE ---

$entity = new MyEntity();
$entity->id = new DomainId('1');

$compositeEntity = new MyCompositeEntity();
$compositeEntity->name = ...;
$compositeEntity->year = ...;

$helper->isIdentity('1'); // false
$helper->isIdentity(new DomainId('1')); // true
$helper->isIdentity($entity); // true

$helper->normalizeIdentifier(new DomainId()); // null
$helper->normalizeIdentifier(new DomainId('1')); // "1"
$helper->normalizeIdentifier('1'); // "1"
$helper->normalizeIdentifier($entity); // "1"
$helper->normalizeIdentifier($compositeEntity); // ['name' => ..., 'year' => ....]

$helper->isIdentity(MyEntity::class, 1); // true
$helper->isIdentity(MyCompositeEntity::class, 1); // false
$helper->isIdentity(MyCompositeEntity::class, ['name' => ...]); // false
$helper->isIdentity(MyCompositeEntity::class, ['name' => ..., 'year' => ...]); // true

$helper->toIdentity(MyEntity::class, 1); // ['id' => 1]
```
