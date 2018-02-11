# Identity mapping

An identity mapping is a domain service and is bound to `MsgPhp\Domain\DomainIdentityMappingInterface`. It tells about
the identifier metadata for a known domain object.

## Implementations

- `MsgPhp\Domain\Infra\InMemory\DomainIdentityMapping`
    - Based on in-memory identity map
- `MsgPhp\Domain\Infra\Doctrine\DomainIdentityMapping`
    - Based on Doctrine's identity map
    - Requires `doctrine/orm`

## API

### `getIdentifierFieldNames(string $class): array`

Returns the identifier field names for `$class`. Any instance should have an identity composed of these field values.

### `getIdentity(object $object): array`

Returns the actual identifier values of `$object`. Each identifier value is keyed by its corresponding identifier field
name.

## Doctrine example

```php
<?php

use MsgPhp\Domain\Infra\Doctrine\DomainIdentityMapping;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity() */
class MyEntity
{
    /** @ORM\Id @ORM\Column(type="string") */
    public $name;

    /** @ORM\Id @ORM\Column(type="integer") */
    public $year;
}

$entity = new MyEntity();
$entity->name = ...;
$entity->year = ...;

/** @var EntityManagerInterface $em */
$em = ...;

$identityMapping = new DomainIdentityMapping($em);

$fields = $identityMapping->getIdentifierFieldNames(MyEntity::class); // ['name', 'year']
$identity = $identityMapping->getIdentity($entity); // ['name' => ..., 'year' => ...]
```
