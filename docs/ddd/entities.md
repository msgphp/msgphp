# Entities

Domain entities are "[vanilla] PHP objects" owned by the user. To simplify its definition common _fields_ and _features_
are provided in the form of PHP [traits].

## Entity Fields

Use entity fields to provide _read operations_ for common entity fields. Built-in fields are:

- `Msgphp\Domain\Entity\Fields\CreatedAtField`
- `Msgphp\Domain\Entity\Fields\LastUpdatedAtField`

## Entity Features

Use entity features to provide _write operations_ for common entity fields. Built-in features are:

- `MsgPhp\Domain\Entity\Features\CanBeConfirmed`
- `MsgPhp\Domain\Entity\Features\CanBeEnabled`

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

[vanilla]: https://en.wikipedia.org/wiki/Plain_vanilla
[traits]: https://secure.php.net/traits
