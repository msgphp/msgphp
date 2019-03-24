# Universally Unique Identifier

An overview of available infrastructural code when working with [UUIDs][uuid].

- Requires [ramsey/uuid]

## Domain Identifier

A UUID tailored [domain identifier](../ddd/identifiers.md) trait is provided by `MsgPhp\Domain\Infrastructure\Uuid\DomainIdTrait`.

### Basic Example

```php
<?php

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Infrastructure\Uuid\DomainIdTrait;
use Ramsey\Uuid\Uuid;

// --- SETUP ---

class MyDomainUuid implements DomainIdInterface
{
    use DomainIdTrait;
}

$id = new MyDomainUuid(); // a new UUID version 4 value
$id = new MyDomainUuid(Uuid::uuid1());
$id = new MyDomainUuid(Uuid::fromString('00000000-0000-0000-0000-000000000000')); 
```

[uuid]: https://en.wikipedia.org/wiki/Universally_unique_identifier
[ramsey/uuid]: https://packagist.org/packages/ramsey/uuid
