# Configuring Doctrine ORM

In this article is explained how to setup [Doctrine ORM infrastructure](../infrastructure/doctrine-orm.md) for any
MsgPHP bundle within a Symfony application.

## Installation

```bash
composer install doctrine/orm doctrine/doctrine-bundle

# with Symfony Flex
composer install orm
```

## Configuration

See the [recipe configuration] for the minimal configuration to put in `config/packages/doctrine.yaml`.

Although the examples use annotation based mappings, you are not required to do so. [Read more][doctrine-bundle-mapping-config].

!!! info
    The configuration is automatically added with Symfony Flex

### Configure an Entity Manager

MsgPHP uses the `doctrine.orm.entity_manager` entity manager service by default. To use another entity manager instead
configure the entity manager alias service:

```yaml
# config/services.yaml

services:
    # ...

    msgphp.doctrine.entity_manager: '@doctrine.orm.other_entity_manager'
```

## Mapping Entities

A MsgPHP bundle provides its base entity models in the form of [mapped superclasses]. An actual entity object must be
defined by your application:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\<DomainName>\Entity\<SomeEntity> as Base<SomeEntity>;

/**
 * @ORM\Entity()
 */
class <SomeEntity> extends Base<SomeEntity>
{
    // ...
}
```

Let MsgPHP know about your entity:

```yaml
# config/packages/msgphp_<domain-name>.yaml

msgphp_<domain-name>: # e.g. msgphp_user
    class_mapping:
        MsgPhp\<DomainName>\Entity\<SomeEntity>: App\Entity\<SomeEntity>
```

!!! info
    With Symfony Flex the required entities of a MsgPHP bundle are automatically created and configured

## Mapping Identifiers

When an entity object is identified using a [domain identifier](../ddd/identifiers.md) we must configure it accordingly:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\<DomainName>\Entity\<SomeEntity> as Base<SomeEntity>;
use MsgPhp\<DomainName>\<SomeEntity>IdInterface;

/**
 * @ORM\Entity()
 */
class <SomeEntity> extends Base<SomeEntity>
{
    /**
     * @var <SomeEntity>IdInterface|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="msghp_some_entity_id")
     */
    private $id;
}
```

!!! note
    When using an identifier that is able to calculate a value upfront (e.g. UUIDs) the `@ORM\GeneratedValue()` is not
    necessary

!!! info
    [Read more](../infrastructure/doctrine-dbal.md#domain-identifier-type) about the Doctrine domain identifier type

### Custom Identifiers

In case a custom implementation is used it should be configured accordingly:

```yaml
# config/packages/msgphp_<domain-name>.yaml

msgphp_<domain-name>: # e.g. msgphp_user
    class_mapping:
        MsgPhp\<DomainName>\<SomeEntity>IdInterface: App\<SomeEntity>Id
    id_type_mapping:
        MsgPhp\<DomainName>\<SomeEntity>IdInterface: bigint
```

### Disable Identifier Nullability

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\<DomainName>\Entity\<SomeEntity> as Base<SomeEntity>;
use MsgPhp\<DomainName>\<SomeEntity>IdInterface;
use MsgPhp\<DomainName>\<SomeEntity>Id;

/**
 * @ORM\Entity()
 */
class <SomeEntity> extends Base<SomeEntity>
{
    /**
     * @var <SomeEntity>IdInterface
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="msghp_some_entity_id")
     */
    private $id;

    public function __construct(<SomeEntity>IdInterface $id)
    {
        $this->id = $id;
    }
    
    // alternatively (coupled)

    public function __construct()
    {
        $this->id = new <SomeEntity>Id();
    }
}
```

### Disable Automatic Identifier Hydration

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\<DomainName>\Entity\<SomeEntity> as Base<SomeEntity>;
use MsgPhp\<DomainName>\<SomeEntity>IdInterface;
use MsgPhp\<DomainName>\<SomeEntity>Id;

/**
 * @ORM\Entity()
 */
class <SomeEntity> extends Base<SomeEntity>
{
    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): <SomeEntity>IdInterface
    {
        return <SomeEntity>Id::fromValue($this->id);
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
