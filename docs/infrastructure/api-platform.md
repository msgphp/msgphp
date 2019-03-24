# API Platform

An overview of available infrastructural code when using [API Platform].

- Requires [api-platform/core]

## Projection Data Provider

When working with [projections](../projection/models.md) an [API Data Provider] is provided by `MsgPhp\Domain\Infrastructure\ApiPlatform\ProjectionDataProvider`.
It uses any [projection repository](../projection/repositories.md) in an effort to provide API resources. 

### Minimal Configuration

```yaml
api_platform:
    # ...

    resource_class_directories:
        - '%kernel.project_dir%/src/Api/Projection'

services:
    # ..
.
    MsgPhp\Domain\Infrastructure\ApiPlatform\ProjectionDataProvider:
        tags: [api_platform.collection_data_provider]
        autowire: true
```

!!! note
    See also [API Platform Configuration] documentation

### Basic Example

```php
<?php

namespace App\Api\Projection;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use MsgPhp\Domain\Projection\Projection;

/**
 * @ApiResource(shortName="Some")
 */
class SomeProjection implements Projection
{
    /**
     * @ApiProperty(identifier=true)
     */
    public $id;

    public static function fromDocument(array $document): Projection
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
