# Elasticsearch

An overview of available infrastructural code when using Elasticsearch's [PHP Api][elasticsearch-project].

- Requires [elasticsearch/elasticsearch]

## Projection Type Registry

An Elasticsearch tailored [projection type registry](../projection/type-registry.md) is provided by `MsgPhp\Domain\Infrastructure\Elasticsearch\ProjectionTypeRegistry`.
It works directly with any [`Client`][api-client] and a known configuration of type information.

- `__construct(Client $client, string $prefix, array $mappings, array $settings = [], LoggerInterface $logger = null)`
    - `$client`: The client to work with
    - `$prefix`: The index prefix
    - `$mappings`: Index mappings keyed by type
    - `$settings`: Index settings keyed by type (use `'*' => [...]` for default settings)
    - `$logger`: An optional [PSR logger]

### Basic Example

```php
<?php

use Elasticsearch\Client;
use MsgPhp\Domain\Infrastructure\Elasticsearch\ProjectionTypeRegistry;
use MsgPhp\Domain\Projection\Projection;

// --- SETUP ---

class MyProjection implements Projection
{
    public $someField;
    public $otherField;

    public static function fromDocument(array $document): Projection
    {
        $projection = new static();
        $projection->someField = $document['some_field'] ?? null;
        $projection->otherField = $document['other_field'] ?? null;

        return $projection;
    }
}

/** @var Client $client */
$client = ...;
$typeRegistry = new ProjectionTypeRegistry($client, 'app_dev-', [
    'my_projection' => [
        'some_field' => 'some_type', // defaults to ['type' => 'some_type']
        'other_field' => [ // defaults to ['type' => 'text', ...]
            // ...
        ],
    ],
]);
```

## Projection Repository

An Elasticsearch tailored [projection repository](../projection/repositories.md) is provided by `MsgPhp\Domain\Infrastructure\Elasticsearch\ProjectionRepository`.
It works directly with any [`Client`][api-client].

- `__construct(Client $client, string $prefix)`
    - `$client`: The Client to work with
    - `$prefix`: The index prefix

### Basic Example

```php
<?php

use Elasticsearch\Client;
use MsgPhp\Domain\Infrastructure\Elasticsearch\ProjectionRepository;

// --- SETUP ---

/** @var Client $client */
$client = ...;
$repository = new ProjectionRepository($client, 'app_dev-');
```

[elasticsearch-project]: https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index.html
[elasticsearch/elasticsearch]: https://packagist.org/packages/elasticsearch/elasticsearch
[api-client]: https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/ElasticsearchPHP_Endpoints.html#Elasticsearch_Client
[PSR logger]: https://www.php-fig.org/psr/psr-3/
