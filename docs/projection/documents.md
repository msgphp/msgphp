# Projection Documents

A projection document is a domain value object of type `MsgPhp\Domain\Projection\DomainProjectionDocument`. Its purpose
is to hold a projection document its data and current state.

## API

### Properties

- `int $status`: The current document status. See also [default statuses][api-statuses].
- `?\Exception $error`: An occurred error, if any
- `?object $source`: The origin object source, if any

---

### `getType(): string`

Gets the projection type. Always must refer to a [projection](models.md) class name.

---

### `getId(): ?string`

Gets the document ID, if any. Otherwise an auto-generated value is implied.

---

### `getBody(): array`

Gets the document body.

---

### `toProjection(): DomainProjectionInterface`

Transforms the document into its projection model.

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

$document = new DomainProjectionDocument(MyProjection::class, null, [
    'some_field' => 'value',
]);

/** @var MyProjection $projection */
$projection = $document->toProjection();
```

[api-statuses]: https://msgphp.github.io/api/MsgPhp/Domain/Projection/DomainProjectionDocument.html#page-content
