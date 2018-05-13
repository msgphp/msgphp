# Projection Repositories

A domain projection repository is bound to `MsgPhp\Domain\Projection\DomainProjectionRepositoryInterface`. Its purpose
is to store and query [projection documents](documents.md).

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

### `MsgPhp\Domain\Infra\Elasticsearch\DomainProjectionRepository`

An Elasticsearch tailored projection repository.

- [Read more](../infrastructure/elasticsearch.md#domain-projection-repository)

## Basic example

```php
<?php

use MsgPhp\Domain\Projection\DomainProjectionInterface;
use MsgPhp\Domain\Projection\DomainProjectionRepositoryInterface;
use MsgPhp\Domain\Projection\ProjectionDocument;

// --- SETUP ---

class MyProjection implements DomainProjectionInterface
{
    public static function fromDocument(array $document): DomainProjectionInterface
    {
        // ...
    }
}

/** @var DomainProjectionRepositoryInterface $repository */
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
