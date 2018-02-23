# Identifiers

A domain identifier is a [string-able] value object and bound to `MsgPhp\Domain\DomainIdInterface`. Its purpose is to
utilize a primitive identifier value.

## API

### Extends

- [`\Serializable`][serializable]
- [`\JsonSerializable`][json-serializable]

---

### `static fromValue($value): DomainIdInterface`

Factorizes a new identifier from its primitive value. Using `null` might imply an empty identifier.

---

### `isEmpty(): bool`

Tells if an identifier value is considered empty, thus has no known primitive value.

---

### `equals(DomainIdInterface $id): bool`

Tells if an identifier equals another identifier.

---

### `toString(): string` / `__toString(): string`

Returns the identifier its primitive string value. If the identifier is empty (see `isEmpty()`) an empty string (`""`) 
should be returned.

## Implementations

### `MsgPhp\Domain\DomainId`

A first class citizen domain identifier. It leverages `string|null` as underlying data type.

- `__construct(string $id = null)`
    - `$id`: The primitive identifier value. In case of `null` an empty identifier is implied.

#### Basic example

```php
<?php

use MsgPhp\Domain\DomainId;

// --- SETUP ---

class OtherDomainId extends DomainId
{
}

$emptyId = new DomainId();
$id = new DomainId('1');

// --- USAGE ---

$emptyId->isEmpty(); // true
$id->isEmpty(); // false

$emptyId->equals($emptyId); // true
$emptyId->equals(new DomainId()); // false
$id->equals(new DomainId('1')); // true
$id->equals(new OtherDomainId('1')); // false due type varying

$emptyId->toString(); // ""
(string) $id; // "1"

$emptyStringId = new DomainId('');
$emptyStringId->isEmpty() ? null : $emptyStringId->toString(); // ""
$emptyId->isEmpty() ? null : $emptyId->toString(); // null
```

### `MsgPhp\Domain\Infra\Uuid\DomainId`

A UUID tailored domain identifier.

- [Read more](../infrastructure/uuid.md#domain-identifier)

[string-able]: https://secure.php.net/manual/en/language.oop5.magic.php#object.tostring
[serializable]: https://secure.php.net/serializable
[json-serializable]: https://secure.php.net/jsonserializable
