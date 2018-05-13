# Projection Repositories

A projection repository is bound to `MsgPhp\Domain\Projection\ProjectionRepositoryInterface`. Its purpose is to store
and query [projection documents](documents.md).

## API

### `findAll(string $type, int $offset = 0, int $limit = 0): ProjectionDocument[]`

Finds all projection documents by type.

---

### `find(string $type, string $id): ?ProjectionDocument`

Finds a single projection document by type and ID. In case its document cannot be found `null` should be returned.

---

### `clear(string $type): void`

Deletes all projection documents by type.

---

### `save(ProjectionDocument $document): void`

Saves a projection document. The document will be available on any subsequent query.

---

### `delete(string $type, string $id): void`

Deletes a projection document by type and ID. The document will be unavailable on any subsequent query.

## Implementations

### `MsgPhp\Domain\Infra\Elasticsearch\ProjectionRepository`

An Elasticsearch tailored projection repository.

- [Read more](../infrastructure/elasticsearch.md#projection-repository)

## Basic example

```php
<?php

use MsgPhp\Domain\Projection\{ProjectionDocument, ProjectionInterface, ProjectionRepositoryInterface};

// --- SETUP ---

class MyProjection implements ProjectionInterface
{
    public static function fromDocument(array $document): ProjectionInterface
    {
        // ...
    }
}

/** @var ProjectionRepositoryInterface $repository */
$repository = ...;

// --- USAGE ---

$id = ...;
$document = $repository->find(MyProjection::class, $id);

if (null === $projection) {
    $document = ProjectionDocument::create(MyProjection::class, $id, [
        'some_field' => 'value',
    ]);
    $repository->save($document);
}
```
