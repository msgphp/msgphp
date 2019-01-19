# Doctrine Database Abstraction Layer

An overview of available infrastructural code when using Doctrine's [Database Abstraction Layer][dbal-project].

- Requires [doctrine/dbal]

## Domain Identifier Type

A translation between the database type and an [identifier](../ddd/identifiers.md) type in PHP is provided by
`MsgPhp\Domain\Infra\Doctrine\DomainIdType`.

### Basic Example

```php
<?php

use Doctrine\DBAL\Types\Type;
use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Infra\Doctrine\DomainIdType;

// --- SETUP ---

class MyDomainId extends DomainId
{
}

class MyDomainIdType extends DomainIdType
{
    public const NAME = 'my_domain_id';
}


// --- USAGE ---

MyDomainIdType::setClass(MyDomainId::class);
MyDomainIdType::setDataType(Type::GUID);

Type::addType(MyDomainIdType::NAME, MyDomainIdType::class);
```

To leverage the tailored [UUID identifier](../infrastructure/uuid.md#domain-identifier) use a data type from
[ramsey/uuid-doctrine] instead.

```php
<?php

use MsgPhp\Domain\Infra\Uuid\DomainId as DomainUuid;
use Ramsey\Uuid\Doctrine\UuidType;

MyDomainIdType::setClass(DomainUuid::class);
MyDomainIdType::setDataType(UuidType::NAME);
```

[dbal-project]: http://www.doctrine-project.org/projects/dbal.html
[doctrine/dbal]: https://packagist.org/packages/doctrine/dbal
[ramsey/uuid-doctrine]: https://packagist.org/packages/ramsey/uuid-doctrine
