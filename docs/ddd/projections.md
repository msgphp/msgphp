# Projections

A domain projection is a model object and bound to `MsgPhp\Domain\Projection\DomainProjectionInterface`. Its purpose is
is to convert raw model data (a document) into a projection model. Projections can be used as e.g. API resources.

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
```
