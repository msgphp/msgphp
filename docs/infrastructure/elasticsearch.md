# Elasticsearch

An overview of available infrastructural code when using Elasticsearch's [PHP Api][elasticsearch-project].

- Requires [elasticsearch/elasticsearch]

## Domain Projection Type Registry

An Elasticsearch tailored [domain projection type registry](../projection/type-registry.md) is provided by `MsgPhp\Domain\Infra\Elasticsearch\DomainProjectionTypeRegistry`.
It works directly with any [`Client`][api-client] and a known configuration of type information.

- `__construct(Client $client, string $index, array $mappings, array $settings = [], LoggerInterface $logger = null)`
    - `$client`: The client to work with
    - `$index`: The index to use
    - `$mappings` / `$settings`: Index management information. [Read more][index management].
    - `$logger`: An optional [PSR logger]

### Basic example

```php
<?php

use Elasticsearch\Client;
use MsgPhp\Domain\Infra\Elasticsearch\DomainProjectionTypeRegistry;
use MsgPhp\Domain\Projection\DomainProjectionInterface;

// --- SETUP ---

class MyProjection implements DomainProjectionInterface
{
    public $someField;
    public $otherField;

    public static function fromDocument(array $document): DomainProjectionInterface
    {
        $projection = new static();
        $projection->someField = $document['some_field'] ?? null;
        $projection->otherField = $document['other_field'] ?? null;

        return $projection;
    }
}

/** @var Client $client */
$client = ...;
$typeRegistry = new DomainProjectionTypeRegistry($client, 'some_index', [
    MyProjection::class => [
        'some_field' => 'some_type', // defaults to ['type' => 'some_type']
        'other_field' => [ // defaults to ['type' => 'text', ...]
            // ...
        ],
    ],
]);
```

### Advanced mapping example

```php
<?php

use Elasticsearch\Client;
use MsgPhp\Domain\Infra\Elasticsearch\DocumentMappingProviderInterface;
use MsgPhp\Domain\Infra\Elasticsearch\DomainProjectionTypeRegistry;
use MsgPhp\Domain\Projection\DomainProjectionInterface;

// --- SETUP ---

class MyProjection implements DomainProjectionInterface, DocumentMappingProviderInterface
{
    // ...

    public static function fromDocument(array $document): DomainProjectionInterface
    {
        // ...
    }

    public static function provideDocumentMappings(): iterable
    {
        yield static::class => [
            'some_field' => 'some_type',
            'other_field' => [
                // ...
            ],
        ];
    }
}

/** @var Client $client */
$client = ...;
$typeRegistry = new DomainProjectionTypeRegistry($client, 'some_index', [
    MyProjection::class,
]);
```

## Domain Projection Repository

An Elasticsearch tailored [domain projection repository](../projection/repositories.md) is provided by `MsgPhp\Domain\Infra\Elasticsearch\DomainProjectionRepository`.
It works directly with any [`Client`][api-client].

- `__construct(Client $client, string $index)`
    - `$client`: The Client to work with
    - `$index`: The index to use

### Basic example

```php
<?php

use Elasticsearch\Client;
use MsgPhp\Domain\Infra\Elasticsearch\DomainProjectionRepository;

// --- SETUP ---

/** @var Client $client */
$client = ...;
$repository = new DomainProjectionRepository($client, 'some_index');
```

[elasticsearch-project]: https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index.html
[elasticsearch/elasticsearch]: https://packagist.org/packages/elasticsearch/elasticsearch
[index management]: https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_index_management_operations.html
[api-client]: https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/ElasticsearchPHP_Endpoints.html#Elasticsearch_Client
[PSR logger]: https://www.php-fig.org/psr/psr-3/
