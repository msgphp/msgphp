# Identifiers

A domain identifier is a value object and bound to `MsgPhp\Domain\DomainIdInterface`. Its purpose is to utilize a
primitive identifier value, usually used to identity an entity with.

## API

### Extends

- [`\Serializable`][serializable]
- [`\JsonSerializable`][json-serializable]

---

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

### `MsgPhp\Domain\DomainId`

A first class citizen domain identifier.

#### Basic Example

```php
<?php

use MsgPhp\Domain\DomainId;

// --- SETUP ---

$id = new DomainId('1');
$emptyId = new DomainId();

// --- USAGE ---

$id->isEmpty(); // false
$emptyId->isEmpty(); // true

$id->equals(new DomainId('1')); // true
$emptyId->equals(new DomainId()); // false

$id->toString(); // "1"
$emptyId->toString(); // ""
```

### `MsgPhp\Domain\Infra\Uuid\DomainId`

A UUID tailored domain identifier.

- [Read more](../infrastructure/uuid.md#domain-identifier)

[serializable]: https://secure.php.net/serializable
[json-serializable]: https://secure.php.net/jsonserializable
