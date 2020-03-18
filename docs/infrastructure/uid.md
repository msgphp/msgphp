# Unique Identifier

An overview of available infrastructural code when working with any type of [UID].

## Domain Identifier

A list of supported UID tailored [domain identifier](../ddd/identifiers.md) implementations.

### `MsgPhp\Domain\Infrastructure\Uid\DomainUuid`

A [UUID] tailored identifier.

- Requires [ramsey/uuid]

### Basic Example

```php
<?php

use MsgPhp\Domain\Infrastructure\Uid\DomainUuid;
use Ramsey\Uuid\Uuid;

// SETUP

$id = new DomainUuid(); // a new UUID version 4 value
$id = new DomainUuid(Uuid::uuid1());
$id = DomainUuid::fromstring('00000000-0000-0000-0000-000000000000');
```

[UID]: https://en.wikipedia.org/wiki/Unique_identifier
[UUID]: https://en.wikipedia.org/wiki/Universally_unique_identifier
[ramsey/uuid]: https://packagist.org/packages/ramsey/uuid
