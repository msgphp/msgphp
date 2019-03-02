# Identifiers

A domain identifier is a value object and bound to `MsgPhp\Domain\DomainIdInterface`. Its purpose is to utilize a
primitive identifier value, usually used to identity an entity with.

## API

### `static fromValue($value): DomainIdInterface`

Returns a factorized identifier from any primitive value. Using `null` might imply an empty identifier.

---

### `isEmpty(): bool`

Tells if an identifier value is considered empty, thus has no known primitive value.

---

### `equals(DomainIdInterface $id): bool`

Tells if an identifier strictly equals another identifier.

---

### `toString(): string` / `__toString(): string`

Returns the identifier its primitive string value. If the identifier is empty (see `isEmpty()`) an empty string should
be returned.

## Implementations

### `MsgPhp\Domain\DomainIdTrait`

A first class citizen domain identifier trait.

#### Basic Example

```php
<?php

use MsgPhp\Domain\{DomainIdInterface, DomainIdTrait};

// --- SETUP ---

class MyDomainId implements DomainIdInterface
{
    use DomainIdTrait;
}

$id = new MyDomainId('1');
$emptyId = new MyDomainId();

// --- USAGE ---

$id->isEmpty(); // false
$emptyId->isEmpty(); // true

$id->equals(new MyDomainId('1')); // true
$emptyId->equals(new MyDomainId()); // false
$emptyId->equals($emptyId); // true

$id->toString(); // "1"
$emptyId->toString(); // ""
```

### `MsgPhp\Domain\Infra\Uuid\DomainId`

A UUID tailored domain identifier.

- [Read more](../infrastructure/uuid.md#domain-identifier)
