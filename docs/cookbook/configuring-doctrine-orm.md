# Configuring Doctrine ORM

To be able to fetch and persist entities using [repositories](../ddd/repositories.md) provided by MsgPHP an [ORM] must
be configured.

In this article is explained how to setup [Doctrine ORM infrastructure](../infrastructure/doctrine-orm.md).

## Installation

```bash
composer require doctrine/orm doctrine/doctrine-bundle

# with Symfony Flex
composer require orm
```

## Configuration

See the [recipe configuration] for the minimal configuration to put in `config/packages/doctrine.yaml`.

Although the examples use annotation based mappings, you are not required to do so. [Read more][doctrine-bundle-mapping-config].

!!! info
    The configuration is automatically added with Symfony Flex

### Configure a Database

```bash
# .env

# sqlite
DATABASE_URL=sqlite:///%kernel.project_dir%/var/db.sqlite

# mysql / mariadb
DATABASE_URL="mysql://user:pass@host:3306/db_name?charset=utf8mb4&serverVersion=5.7"
```

Create the database:

```bash
bin/console doctrine:database:create
bin/console doctrine:schema:update
```

### Configure an Entity Manager

MsgPHP uses the `doctrine.orm.entity_manager` entity manager service by default. To use another entity manager instead
configure the entity manager alias service:

```yaml
# config/services.yaml

services:
    # ...

    msgphp.doctrine.entity_manager: '@doctrine.orm.other_entity_manager'
```

## Overriding Mapping Configuration

Use a fixed max key length:

```yaml
# config/services.yaml

parameters:
    msgphp.doctrine.mapping_config:
        key_max_length: 191
```

By default MsgPHPs [built-in mapping files] can be overridden by putting a modified version in `%kernel.project_dir%/config/msgphp/doctrine`.
To specify a different location, use:

```yaml
# config/services.yaml

parameters:
    msgphp.doctrine.mapping_config:
        mapping_dir: /some/path
```

[ORM]: https://en.wikipedia.org/wiki/Object-relational_mapping
[recipe configuration]: https://github.com/symfony/recipes/blob/master/doctrine/doctrine-bundle/1.6/config/packages/doctrine.yaml
[doctrine-bundle-mapping-config]: https://symfony.com/doc/master/bundles/DoctrineBundle/configuration.html#mapping-configuration
[mapped superclasses]: https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html#mapped-superclasses
[built-in mapping files]: https://github.com/msgphp/msgphp/tree/master/src/User/Infrastructure/Doctrine/Resources/dist-mapping
