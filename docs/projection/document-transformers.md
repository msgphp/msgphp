# Projection Document Transformers

A projection document transformer is bound to `MsgPhp\Domain\Projection\DomainProjectionDocumentTransformerInterface`.
Its purpose is to transform domain objects into [projection documents](documents.md).

## API

### `transform(object $object): DomainProjectionDocument`

Transforms the domain object into a projection document.

## Basic example

```php
<?php

use MsgPhp\Domain\Projection\DomainProjectionDocument;
use MsgPhp\Domain\Projection\DomainProjectionDocumentTransformerInterface;
use MsgPhp\Domain\Projection\DomainProjectionInterface;

// --- SETUP ---

class MyEntity
{
    public $id;
    public $someField;

    public function __construct($id)
    {
        $this->id = $id;
    }
}

class MyProjection implements DomainProjectionInterface
{
    public $id;
    public $someField;

    public static function fromDocument(array $document): DomainProjectionInterface
    {
        $projection = new static();
        $projection->id = $document['id'] ?? null;
        $projection->someField = $document['some_field'] ?? null;

        return $projection;
    }
}

class MyTransformer implements DomainProjectionDocumentTransformerInterface
{
    public function transform($object): DomainProjectionDocument
    {
        if ($object instanceof MyEntity) {
            return new DomainProjectionDocument(MyProjection::class, $object->id, [
                'id' => $object->id,
                'some_field' => $object->someField,
            ]);
        }

        throw new \LogicException();
    }
}

$transformer = new MyTransformer();

// --- USAGE ---

$entity = new MyEntity(1);
$document = $transformer->transform($entity);
$projection = $document->toProjection();
```
