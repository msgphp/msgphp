# Projection Documents

A projection document is a value object of type `MsgPhp\Domain\Projection\ProjectionDocument`. Its purpose is to hold a
projection document its data and current state.

## API

### Properties

- `int $status`: The current document status. See also [default statuses][api-statuses].
- `?\Throwable $error`: An occurred error, if any
- `?object $source`: The source object, if any

---

### `getType(): string`

Gets the projection type and refers to a [projection](models.md) class name.

---

### `getId(): ?string`

Gets the document ID, if any. Otherwise an auto-generated value is implied.

---

### `getBody(): array`

Gets the document data.

---

### `toProjection(): Projection`

Transforms the document into its projection model.

## Basic example

```php
<?php

use MsgPhp\Domain\Projection\Projection;
use MsgPhp\Domain\Projection\ProjectionDocument;

// --- SETUP ---

class MyProjection implements Projection
{
    public static function fromDocument(array $document): Projection
    {
        // ...
    }
}

$document = new ProjectionDocument(MyProjection::class, null, [
    'some_field' => 'value',
]);

/** @var MyProjection $projection */
$projection = $document->toProjection();
```

[api-statuses]: https://msgphp.github.io/api/MsgPhp/Domain/Projection/ProjectionDocument.html#page-content
