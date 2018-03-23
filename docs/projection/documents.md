# Projection documents

A projection document is a domain value object of type `MsgPhp\Domain\Projection\DomainProjectionDocument`. Its purpose
is to hold a projection document its (meta)data and current state.

## API

### `static create(string $type, string $id = null, array $body = []): DomainProjectionDocument`

Creates a projection document for a known projection type. In case `$id` is not given it implies an auto-generated
value. A projection `$type` usually refers to a known [projection](models.md) by class name.

## Basic example

```php
<?php

use MsgPhp\Domain\Projection\DomainProjectionInterface;
use MsgPhp\Domain\Projection\DomainProjectionDocument;

// --- SETUP ---

class MyProjection implements DomainProjectionInterface
{
    public static function fromDocument(array $document): DomainProjectionInterface
    {
        // ...
    }
}

$document = DomainProjectionDocument::create(MyProjection::class, null, [
    'some_field' => 'value',
]);
```
