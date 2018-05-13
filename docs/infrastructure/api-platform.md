# API Platform

An overview of available infrastructural code when using [API Platform].

- Requires [api-platform/core]

## Domain Projection Data Provider

When working with [projections](../projection/models.md) an [API Data Provider] is provided by `MsgPhp\Domain\Infra\ApiPlatform\DomainProjectionDataProvider`.
It uses any [projection repository](../projection/repositories.md) in an effort to provide API resources. 

### Minimal configuration

```yaml
api_platform:
    resource_class_directories:
        - '%kernel.project_dir%/src/Api/Projection'

    # ...

services:
    MsgPhp\Domain\Infra\ApiPlatform\DomainProjectionDataProvider:
        tags: [api_platform.collection_data_provider]
        autowire: true

    # ...
```

!!! note
    See also [API Platform Configuration] documentation

### Basic example

```php
<?php

namespace App\Api\Projection;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use MsgPhp\Domain\Projection\DomainProjectionInterface;

/**
 * @ApiResource(shortName="Some")
 */
class SomeProjection implements DomainProjectionInterface
{
    /**
     * @ApiProperty(identifier=true)
     */
    public $id;

    public static function fromDocument(array $document): DomainProjectionInterface
    {
        $projection = new self();
        $projection->id = $document['id'] ?? null;

        return $projection;
    }
}
```

[API Platform]: https://api-platform.com/
[api-platform/core]: https://packagist.org/packages/api-platform/core
[API Data Provider]: https://api-platform.com/docs/core/data-providers
[API Platform Configuration]: https://api-platform.com/docs/core/configuration
