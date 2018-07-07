# Configuring Doctrine ORM

After a [bundle](bundle-installation.md) is installed we can configure [Doctrine ORM](../infrastructure/doctrine-orm.md).

## Installation

```bash
composer install doctrine/orm doctrine/doctrine-bundle

# with Symfony Flex
composer install orm
```

## Configuration

See the Doctrine Bundle [recipe configuration] for the minimal configuration to put in `config/packages/doctrine.yaml`.

Although the examples use annotation based mappings, you are not required to do so. [Read more][doctrine-bundle-mapping-config].

!!! info
    The configuration is automatically added with Symfony Flex

By default MsgPHP uses the `doctrine.orm.entity_manager` service. You can override its alias service to use any other
entity manager:

```yaml
# config/services.yaml

services:
    msgphp.doctrine.entity_manager: '@doctrine.orm.some_other_entity_manager'

    # ...
```

## Mapping Entities

A MsgPHP bundle provides base entity models in the form of [mapped superclasses]. An actual entity object must be
defined by your application:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\SomeDomain\Entity\SomeEntity as BaseSomeEntity;

/**
 * @ORM\Entity()
 */
class SomeEntity extends BaseSomeEntity
{
    // ...
}
```

Let MsgPHP know about your entity:

```yaml
msgphp_<name>:
    class_mapping:
        MsgPhp\SomeDomain\Entity\SomeEntity: App\Entity\SomeEntity

    # ...
```

!!! note
    See the [reference](../reference/entities.md) page for all available entities provided per domain. When using a
    entity field / feature trait its default ORM mapping is configured automatically.

!!! info
    With Symfony Flex the required entities of a MsgPHP bundle are automatically created, including ORM mapping
    and configuration

## Mapping Identifiers

When an entity object is identified using a [domain identifier](../ddd/identifiers.md) we must configure it accordingly:

```php
<?php

use MsgPhp\SomeDomain\SomeIdInterface;

// ...

class SomeEntity extends BaseSomeEntity
{
    /**
     * @var SomeIdInterface|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="msghp_some_id")
     */
    public $id;
}
```

!!! note
    See the [reference](../reference/doctrine-identifier-types.md) page for all available identifier types provided per
    domain

!!! note
    When using an identifier that is able to calculate a value upfront (e.g. UUIDs) the `@ORM\GeneratedValue()` is not
    necessary

!!! info
    [Read more](../infrastructure/doctrine-dbal.md#domain-identifier-type) about the Doctrine domain identifier type

### Custom Identifiers

In case a custom implementation is used it should be configured accordingly:

```yaml
msgphp_<name>:
    class_mapping:
        MsgPhp\SomeDomain\SomeIdInterface: App\SomeId
    id_type_mapping:
        MsgPhp\SomeDomain\SomeIdInterface: bigint

    # ...
```

### Without Nullability

```php
<?php

// ...

class SomeEntity extends BaseSomeEntity
{
    /**
     * @ORM\...
     */
    private $id;

    public function __construct(SomeIdInterface $id)
    {
        $this->id = $id;
    }

    public function getId(): SomeIdInterface
    {
        return $this->id;
    }
}
```

Or coupled with a known identifier class:

```php
<?php

use MsgPhp\SomeDomain\SomeId;

// ...

class SomeEntity extends BaseSomeEntity
{
    // ...

    public function __construct()
    {
        $this->id = new SomeId();
    }

    // ...
}
```

### Without Automatic Hydration

To hydrate the primitive identifier value instead of a value object, the type can be configured regularly. It requires
to couple with a known identifier class.

```php
<?php

// ...

class SomeEntity extends BaseSomeEntity
{
    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function __construct(SomeIdInterface $id)
    {
        $this->id = $id->isEmpty() ? null : (int) $id->toString();
    }

    public function getId(): SomeIdInterface
    {
        return SomeId::fromValue($this->id);
    }
}
```

!!! info
    [Read more](../infrastructure/doctrine-orm.md#domain-identifier-hydration) about Doctrine identifier hydration

## Database Setup

Configure the database URL:

```bash
# .env

# sqlite
DATABASE_URL=sqlite:///%kernel.project_dir%/var/db.sqlite

# mysql / mariadb
DATABASE_URL="mysql://user:pass@host:3306/db_name?charset=utf8mb4&serverVersion=5.7"
```

Create the database schema:

```bash
bin/console doctrine:database:create
bin/console doctrine:schema:update --force
```

[recipe configuration]: https://github.com/symfony/recipes/blob/master/doctrine/doctrine-bundle/1.6/config/packages/doctrine.yaml
[doctrine-bundle-mapping-config]: https://symfony.com/doc/master/bundles/DoctrineBundle/configuration.html#mapping-configuration
[mapped superclasses]: https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html#mapped-superclasses
