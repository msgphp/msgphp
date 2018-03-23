# Projections

A domain projection is a model object and bound to `MsgPhp\Domain\Projection\DomainProjectionInterface`. Its purpose is
to convert raw model data (a document) into a projection, enabling to be stored separate.

The document is usually a transformation from a domain object (e.g. an entity) and therefor projections should be
considered read-only and disposable, as they can be re-created / synchronized at any time from a source of truth.

A practical use case for domain projections are APIs, where each API resource is a so called projection from a
corresponding entity. It enables decoupling and thus optimized API responses.

For integration with [API Platform] see also the [domain projection data provider](../infrastructure/api-platform.md#domain-projection-data-provider).

## API

### `static fromDocument(array $document): DomainProjectionInterface`

Creates a projection from raw document data.

## Basic example

```php
<?php

use MsgPhp\Domain\Projection\DomainProjectionInterface;

// --- SETUP ---

class MyProjection implements DomainProjectionInterface
{
    public $someField;

    public static function fromDocument(array $document): DomainProjectionInterface
    {
        $projection = new self();
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
