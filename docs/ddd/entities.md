# Entities

Domain entities are "[vanilla] PHP objects" owned by the user. To simplify its definition common _fields_ and _features_
are provided in the form of PHP [traits].

Fields can be compared to a read-operation, whereas features represent a read/write-operation. They can be discovered
in the following namespaces:

- `Msgphp\Domain\Entity\Fields\`
- `MsgPhp\Domain\Entity\Features\`

## Basic Example

```php
<?php

use MsgPhp\Domain\Entity\Fields\CreatedAtField;
use MsgPhp\Domain\Entity\Features\CanBeEnabled;

// --- SETUP ---

class MyEntity
{
    use CreatedAtField;
    use CanBeEnabled;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}

// --- USAGE ---

$entity = new MyEntity();
$createdAt = $entity->getCreatedAt();

if (!$entity->isEnabled()) {
    $entity->enable();
}
```

!!! note
    See the [reference](../reference/entities.md#msgphpdomain) page for all available fields and features

[vanilla]: https://en.wikipedia.org/wiki/Plain_vanilla
[traits]: https://secure.php.net/traits
