# Identities

`MsgPhp\Domain\DomainIdentityHelper` is a utility domain service. Its purpose is to ease working with domain identities
and the [identity mapping](identity-mapping.md).

A domain identity is a composite value (`array`) of one or more individual identifier values, indexed by an identifier
field name. Its usage is to uniquely identify a domain object, thus qualifying it an entity object.

Identifier values can be of any type; a [domain identifier](identifiers.md), another (foreign) entity object, or any
primitive value.

A single identifier value might represent an actual identity in case it's composed by a single identifier field.

## API

### `isIdentifier($value): bool`

Tells if `$value` is a known identifier value. This is either a [domain identifier](identifiers.md) object or an entity
object.

---

### `isEmptyIdentifier($value): bool`

Tells if `$value` is a known empty identifier value. It returns `true` if the specified value is either `null`, an empty
[domain identifier](identifiers.md) or an entity object without its identity set.

---

### `normalizeIdentifier($value)`

Returns the primitive identifier value of `$value`. Empty identifier values (see `isEmptyIdentifier()`) are normalized
as `null`, a [domain identifier](identifiers.md) as string value and an entity object as normalized identity value.
A value of any other type is returned as is.

---

### `getIdentifiers(object $object): array`

Returns the actual identifier values of `$object`.

---

### `getIdentifierFieldNames(string $class): array`

Returns the identifier field names for `$class`. Any instance should have an identity composed of these field values.
See also `DomainIdentityMappingInterface::getIdentifierFieldNames()`.

---

### `isIdentity(string $class, $value): bool`

Tells if `$value` is a valid identity for type `$class`. An identity value is considered valid if an entity object uses
a single identifier value as identity and `$value` is a non empty identifier (see `isEmptyIdentifier()`).

In case of one or more identifier values, given in the form of an array, its keys must exactly match the available
identifier field names and its values must contain no empty identifiers.

---

### `toIdentity(string $class, $value): array`

Returns a composite identity value for `$class` from `$value`.

---

### `getIdentity(object $object): array`

Returns the actual, non empty, identifier values of `$object`. Each identifier value is keyed by its corresponding
identifier field name. See also `DomainIdentityMappingInterface::getIdentity()`.

## Basic example

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

$helper->getIdentifiers($entity); // [<id>]
$helper->getIdentifiers($compositeEntity); // [<name>, <year>]

$helper->isIdentity(MyEntity::class, 1); // true
$helper->isIdentity(MyCompositeEntity::class, 1); // false
$helper->isIdentity(MyCompositeEntity::class, ['name' => ...]); // false
$helper->isIdentity(MyCompositeEntity::class, ['name' => ..., 'year' => ...]); // true

$helper->toIdentity(MyEntity::class, 1); // ['id' => 1]
```
