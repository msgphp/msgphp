# Identifiers

A domain identifier is a value object and bound to `MsgPhp\Domain\DomainId`. Its purpose is to utilize a primitive
identifier value, usually used to identity an entity with.

## API

### `isNil(): bool`

Tells if an identifier value is nil, thus is considered empty/unknown.

### `equals($other): bool`

Tells if an identifier strictly equals another identifier.

### `toString(): string`

Returns the identifier its primitive string value.

## Implementations

### `MsgPhp\Domain\GenericDomainId`

A generic identifier compatible with any `string` value.

#### Basic Example

```php
<?php

use MsgPhp\Domain\GenericDomainId;

// SETUP

$id = new GenericDomainId('1');
$id = GenericDomainId::fromInt(1);
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

use MsgPhp\Domain\AbstractDomainId;

// SETUP

class MyDomainId extends AbstractDomainId
{
}

class MyOtherDomainId extends AbstractDomainId
{
}

$id = MyDomainId::fromInt(1);
$otherId = MyOtherDomainId::fromInt(1);

// USAGE

$id->equals($otherId); // false
```

### Infrastructural

- [Unique Identifier](../infrastructure/uid.md#domain-identifier) (UUID, ULID, etc.)
