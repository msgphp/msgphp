# Doctrine Database Abstraction Layer

An overview of available infrastructural code when using Doctrine's [Database Abstraction Layer][dbal-project].

- Requires [doctrine/dbal]

## Domain Identifier Type

A translation between the database type and an [identifier](../ddd/identifiers.md) type in PHP is provided by
`MsgPhp\Domain\Infrastructure\Doctrine\DomainIdType`.

### Basic Example

```php
<?php

use Doctrine\DBAL\Types\Type;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainIdType;

// SETUP

class MyDomainIdType extends DomainIdType
{
    public const NAME = 'my_domain_id';
}

// USAGE

MyDomainIdType::setClass(MyDomainId::class);
MyDomainIdType::setDataType(Type::GUID);

Type::addType(MyDomainIdType::NAME, MyDomainIdType::class);
```

To leverage a tailored [UUID identifier](../infrastructure/uid.md#domain-identifier) use one of the UUID data types
provided by [ramsey/uuid-doctrine].

```php
<?php

use Ramsey\Uuid\Doctrine\UuidType;

MyDomainIdType::setClass(MyDomainUuid::class);
MyDomainIdType::setDataType(UuidType::NAME);
```

[dbal-project]: http://www.doctrine-project.org/projects/dbal.html
[doctrine/dbal]: https://packagist.org/packages/doctrine/dbal
[ramsey/uuid-doctrine]: https://packagist.org/packages/ramsey/uuid-doctrine
