# Universally Unique Identifier

An overview of available infrastructural code when working with [UUIDs][uuid].

- Requires [ramsey/uuid]

## Domain Identifier

A UUID tailored [domain identifier](../ddd/identifiers.md) is provided by `MsgPhp\Domain\Infra\Uuid\DomainId`.

### Basic Example

```php
<?php

use MsgPhp\Domain\Infra\Uuid\DomainId;
use Ramsey\Uuid\Uuid;

// --- SETUP ---

$id = new DomainId(); // a new UUID version 4 value
$id = new DomainId(Uuid::uuid1());
$id = new DomainId(Uuid::fromString('00000000-0000-0000-0000-000000000000')); 
```

[uuid]: https://en.wikipedia.org/wiki/Universally_unique_identifier
[ramsey/uuid]: https://packagist.org/packages/ramsey/uuid
