# Identifiers

A domain identifier is a value object and bound to `MsgPhp\Domain\DomainId`. Its purpose is to utilize a primitive
identifier value, usually used to identity an entity with.

## API

### `static fromValue(mixed $value): DomainId`

Returns a factorized identifier from any primitive value. Using `null` might either imply an nil/empty identifier or a
self-generated identifier value.

### `isNil(): bool`

Tells if an identifier value is nil, thus is considered empty/unknown.

### `equals($other): bool`

Tells if an identifier strictly equals another identifier.

### `toString(): string`

Returns the identifier its primitive string value.

## Implementations

### `MsgPhp\Domain\GenericDomainId`

A generic identifier compatible with any `string` or `numeric` value.

#### Basic Example

```php
<?php

use MsgPhp\Domain\GenericDomainId;

// SETUP

$id = new GenericDomainId('1');
$id = GenericDomainId::fromValue(1);
$nilId = new GenericDomainId();

// USAGE

$id->isNil(); // false
$nilId->isNil(); // true

$id->toString(); // "1"
$nilId->toString(); // ""

$id->equals(new GenericDomainId('1')); // true
$id->equals('1'); // false
$id->equals(new GenericDomainId('2')); // false
$id->equals(new GenericDomainId()); // false
```

### `MsgPhp\Domain\AbstractDomainId`

A decorating identifier to built custom/concrete identifiers upon.

#### Basic Example

```php
<?php

use MsgPhp\Domain\AbstractDomainId;use MsgPhp\Domain\DomainId;

// SETUP

class MyDomainId extends AbstractDomainId
{
}

class MyOtherDomainId extends AbstractDomainId
{
}

$id = MyDomainId::fromValue(1);
$otherId = MyOtherDomainId::fromValue(1);

// USAGE

$id->equals($otherId); // false
```

### Infrastructural

- [Unique Identifier](../infrastructure/uid.md#domain-identifier) (UUID, ULID, etc.)
