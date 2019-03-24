# Projection Type Registry

A projection type registry is bound to `MsgPhp\Domain\Projection\ProjectionTypeRegistry`. Its purpose is to manage all
available [projection](models.md) type information.

## API

### `all(): string[]`

Returns all available projection types for this registry.

---

### `initialize(): void`

Initializes the type registry. Usually needs to be called only once per environment, or after any type information has
changed.

---

### `destroy(): void`

Destroys the type registry and thus requires to be re-initialized after.

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

echo 'Initialized types: '.implode(', ', $typeRegistry->all());
```

## Command Line Interface

The type registry can be initialized using the CLI when working with Symfony Console.

- [Read more](../infrastructure/symfony-console.md#initializeprojectiontypescommand)
