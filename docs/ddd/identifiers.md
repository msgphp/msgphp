# Identifiers

A domain identifier is a value object and bound to `MsgPhp\Domain\DomainId`. Its purpose is to utilize a primitive
identifier value, usually used to identity an entity with.

## API

### `static fromValue(mixed $value): DomainId`

Returns a factorized identifier from any primitive value. Using `null` might imply an empty identifier.

---

### `isNil(): bool`

Tells if an identifier value is nil, thus is considered empty/unknown.

---

### `equals($other): bool`

Tells if an identifier strictly equals another identifier.

---

### `toString(): string`

Returns the identifier its primitive string value.

## Implementations

### `MsgPhp\Domain\DomainIdTrait`

A first-class citizen domain identifier trait compatible with any string or numeric value.

#### Basic Example

```php
<?php

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\DomainIdTrait;

// --- SETUP ---

class MyDomainId implements DomainId
{
    use DomainIdTrait;
}

$id = new MyDomainId('1');
$emptyId = new MyDomainId();

// --- USAGE ---

$id->isNil(); // false
$emptyId->isNil(); // true

$id->toString(); // "1"
$emptyId->toString(); // ""

$id->equals(new MyDomainId('1')); // true
$id->equals('1'); // false
$id->equals(new MyDomainId('2')); // false
```

### `MsgPhp\Domain\Infrastructure\Uuid\DomainIdTrait`

A UUID tailored domain identifier trait.

- [Read more](../infrastructure/uuid.md#domain-identifier)
