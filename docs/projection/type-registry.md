# Projection Type Registry

A projection type registry is bound to `MsgPhp\Domain\Projection\ProjectionTypeRegistry`. Its purpose is to manage all
available [projection](models.md) type information.

## API

### `initialize(string ...$type): void`

Initializes the registry. Usually needs to be called only once per environment, or after any type information has
changed.

---

### `destroy(string ...$type): void`

Destroys the registry and thus requires to be re-initialized after.

---

### `lookup(string ...$name): string`

Lookup a type name for an arbitrary name.

## Implementations

### `MsgPhp\Domain\Infrastructure\Elasticsearch\ProjectionTypeRegistry`

An Elasticsearch tailored projection type registry.

- [Read more](../infrastructure/elasticsearch.md#projection-type-registry)

## Basic example

```php
<?php

use MsgPhp\Domain\Projection\ProjectionTypeRegistry;

// --- SETUP ---

/** @var ProjectionTypeRegistry $typeRegistry */
$typeRegistry = ...;

// --- USAGE ---

$typeRegistry->destroy();
$typeRegistry->initialize();
```
