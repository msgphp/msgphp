# Identifiers

An identifier is a domain value object and is bound to `MsgPhp\Domain\DomainIdInterface`. Its purpose is to identify
domain objects, e.g. entities.

## Implementations

- `MsgPhp\Domain\DomainId`
    - Generic scalar values
- `MsgPhp\Domain\Infra\Uuid\DomainId`
    - UUID values
    - Requires `ramsey/uuid`

## API

### `static fromValue($value): DomainIdInterface`

Factorizes a new identifier from a primitive value.

```php
<?php

use MsgPhp\Domain\DomainId;

$id = DomainId::fromValue(1); // allowed
$id = new DomainId(1); // not allowed
$id = new DomainId('1'); // allowed
```

---

### `isEmpty(): bool`

Tells if an identifier value is considered empty. In general this is the case when an identifier is created from a
primitive `null` value, and therefor enables to differ its string value from an explicit empty string value (`""`).

```php
<?php

use MsgPhp\Domain\DomainId;

$id = new DomainId();
$value = $id->isEmpty() ? null : (string) $id; // null

$id = new DomainId('');
$value = $id->isEmpty() ? null : (string) $id; // ""
```

---

### `equals(): bool`

Tells if an identifier equals another identifier. Default implementations vary on type (including UUID). Meaning the
same identifier value is considered **not** equal when comparing e.g.:

```php
<?php

use MsgPhp\Domain\DomainId;

class MyDomainId extends DomainId { }

DomainId::fromValue('1')->equals(MyDomainId::fromValue('1')); // false!
```

---

### `toString(): string` / `__toString(): string`

Returns the string value of the identifier. If the the identifier is empty (see `isEmpty`) an empty string (`""`) 
should be returned.

```php
<?php

use MsgPhp\Domain\DomainId;

echo (new DomainId('1'))->toString();
echo new DomainId('2');
```

## UUID example

```php
<?php

use MsgPhp\Domain\Infra\Uuid\DomainId;
use Ramsey\Uuid\Uuid;

$uuid4 = DomainId::fromValue('cf3d2f85-6c86-44d1-8634-af51c91a9a74');
$uuid4Alt = DomainId::fromValue(Uuid::fromString('cf3d2f85-6c86-44d1-8634-af51c91a9a74'));
$uuid4Alt2 = DomainId::fromValue(Uuid::uuid4());
$newUuid4 = new DomainId();
$newUuid5 = new DomainId(Uuid::uuid5(Uuid::NAMESPACE_URL, 'http://php.net/'));
```
