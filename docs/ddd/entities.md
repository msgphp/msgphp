# Entities

Entity objects are provided per domain layer and usually follow a [POPO] design. To simplify its definition common
fields and features are provided in the form of PHP [traits]. Fields can be compared to a read-operation, whereas
features represent a read/write-operation.

They are defined in a dedicated namespace for discovery, respectively `Msgphp\Domain\Entity\Fields\` and
`MsgPhp\Domain\Entity\Features\`. Additionally more specific fields and features can be provided per domain layer.

!!! note
    See the [reference](../reference/entities.md) page for all available entities provided per domain

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

[POPO]: https://stackoverflow.com/questions/41188002/what-does-the-term-plain-old-php-object-popo-exactly-mean
[traits]: https://secure.php.net/traits
