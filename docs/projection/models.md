# Projections

A projection is a model object and bound to `MsgPhp\Domain\Projection\Projection`. Its purpose is to convert raw model
data (a document) into a projection.

The document is usually a transformation from a domain object (e.g. an entity) and therefor projections should be
considered read-only and disposable, as they can be re-created / synchronized at any time from a source of truth.

A practical use case for projections are APIs, where each API resource is a so called projection from a corresponding
entity. It enables decoupling and thus optimized API responses.

## API

### `static fromDocument(array $document): Projection`

Creates a projection from raw document data.

## Basic example

```php
<?php

use MsgPhp\Domain\Projection\Projection;

// --- SETUP ---

class MyProjection implements Projection
{
    public $someField;

    public static function fromDocument(array $document): Projection
    {
        $projection = new static();
        $projection->someField = $document['some_field'] ?? null;

        return $projection;
    }
}

// --- USAGE ---

$projection = MyProjection::fromDocument([
    'some_field' => 'value',
]);
```
