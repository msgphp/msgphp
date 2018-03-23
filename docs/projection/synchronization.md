# Projection synchronization

`MsgPhp\Domain\Projection\DomainProjectionSynchronization` is a utility domain service. Its purpose is to ease
synchronizing provided [projections](models.md).

So called _providers_ actually provide domain objects, which are in turn transformed to [documents](documents.md) using
any [transformer](document-transformers.md). A document is then stored using any [repository](repositories.md).

## API

### `synchronize(): iterable`

Yields a projection document for each provided domain object regarding its state. The actual document status can be
read from [`ProjectionDocument::$status`][api-projection-document-status].

[api-projection-document-status]: https://msgphp.github.io/api/MsgPhp/Domain/Projection/DomainProjectionSynchronization.html#property_status

## Basic example

```php
<?php

use MsgPhp\Domain\Projection\DomainProjectionDocument;
use MsgPhp\Domain\Projection\DomainProjectionDocumentTransformerInterface;
use MsgPhp\Domain\Projection\DomainProjectionRepositoryInterface;
use MsgPhp\Domain\Projection\DomainProjectionSynchronization;
use MsgPhp\Domain\Projection\DomainProjectionTypeRegistryInterface;

// --- SETUP ---

class MyEntity
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}

/** @var DomainProjectionDocumentTransformerInterface $transformer */
$transformer = ...;
/** @var DomainProjectionTypeRegistryInterface $typeRegistry */
$typeRegistry = ...;
/** @var DomainProjectionRepositoryInterface $repository */
$repository = ...;
$synchronization = new DomainProjectionSynchronization($typeRegistry, $repository, $transformer, [
    function (): iterable {
        yield new MyEntity(1);
        yield new MyEntity(2);
    },
]);

// --- USAGE ---

foreach ($synchronization->synchronize() as $document) {
    if (DomainProjectionDocument::STATUS_VALID === $document->status) {
        echo 'Synchronized projection for '.get_class($document->source).' with ID '.$document->source->id.PHP_EOL;
        continue;
    }

    echo 'Invalid projection for '.get_class($document->source).' with ID '.$document->source->id.PHP_EOL;

    if (null !== $document->error) {
        echo 'An error occurred for '.get_class($document->source).' with ID '.$document->source->id.PHP_EOL;
        echo $document->error->getMessage().' in '.$document->error->getFile().' at '.$document->error->getLine().PHP_EOL;
    }
}
```

## Command Line Interface

A synchronization can be ran using the CLI when working with Symfony Console.

- [Read more](../infrastructure/symfony-console.md#synchronizedomainprojectionscommand)
