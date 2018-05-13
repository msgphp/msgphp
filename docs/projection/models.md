# Projections

A projection is a model object and bound to `MsgPhp\Domain\Projection\ProjectionInterface`. Its purpose is to
convert raw model data (a document) into a projection.

The document is usually a transformation from a domain object (e.g. an entity) and therefor projections should be
considered read-only and disposable, as they can be re-created / synchronized at any time from a source of truth.

A practical use case for projections are APIs, where each API resource is a so called projection from a corresponding
entity. It enables decoupling and thus optimized API responses.

!!! info
    For integration with [API Platform] see the [projection data provider](../infrastructure/api-platform.md#projection-data-provider)

## API

### `static fromDocument(array $document): ProjectionInterface`

Creates a projection from raw document data.

## Basic example

```php
<?php

use MsgPhp\Domain\Projection\ProjectionInterface;

// --- SETUP ---

class MyProjection implements ProjectionInterface
{
    public $someField;

    public static function fromDocument(array $document): ProjectionInterface
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

[API Platform]: https://api-platform.com/
