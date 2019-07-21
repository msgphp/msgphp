# Projection Repositories

A projection repository is bound to `MsgPhp\Domain\Projection\ProjectionRepository`. Its purpose is to store and query
projection documents.

## API

### `find(string $type, string $id): ?array`

Finds a single projection document by type and ID. In case the document cannot be found `null` should be returned.

---

### `save(string $type, array $document): void`

Saves a projection document. The document will be available on any subsequent query.

---

### `saveAll(string $type, iterable<int, array> $documents): void`

Saves all projection documents at once. The documents will be available on any subsequent query.

---

### `delete(string $type, string $id): bool`

Deletes a projection document by type and ID. The document will be unavailable on any subsequent query. A boolean return
value indicates the document was actually deleted yes or no.

## Implementations

### `MsgPhp\Domain\Infrastructure\Elasticsearch\ProjectionRepository`

An Elasticsearch tailored projection repository.

- [Read more](../infrastructure/elasticsearch.md#projection-repository)

## Basic example

```php
<?php

use MsgPhp\Domain\Projection\Projection;
use MsgPhp\Domain\Projection\ProjectionRepository;

// --- SETUP ---

class MyProjection implements Projection
{
    public static function fromDocument(array $document): Projection
    {
        // ...
    }
}

/** @var ProjectionRepository $repository */
$repository = ...;

// --- USAGE ---

$id = ...;
$document = $repository->find('my_projection', $id);

if (null === $document) {
    $document = ['id' => $id, 'some_field' => 'value'];
    $repository->save('my_projection', $document);
}
```
