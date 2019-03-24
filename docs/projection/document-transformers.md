# Projection Document Transformers

A projection document transformer is bound to `MsgPhp\Domain\Projection\ProjectionDocumentTransformer`. Its purpose is
to transform arbitrary objects into [projection documents](documents.md).

## API

### `transform(object $object): ProjectionDocument`

Transforms the domain object into a projection document.

## Basic example

```php
<?php

use MsgPhp\Domain\Projection\Projection;
use MsgPhp\Domain\Projection\ProjectionDocument;
use MsgPhp\Domain\Projection\ProjectionDocumentTransformer;

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

class MyProjection implements Projection
{
    public $id;
    public $someField;

    public static function fromDocument(array $document): Projection
    {
        $projection = new static();
        $projection->id = $document['id'] ?? null;
        $projection->someField = $document['some_field'] ?? null;

        return $projection;
    }
}

class MyTransformer implements ProjectionDocumentTransformer
{
    public function transform($object): ProjectionDocument
    {
        if ($object instanceof MyEntity) {
            return new ProjectionDocument(MyProjection::class, $object->id, [
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
