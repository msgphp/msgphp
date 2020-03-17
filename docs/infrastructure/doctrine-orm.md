# Doctrine Object Relational Mapper

An overview of available infrastructural code when using Doctrine's [Object Relational Mapper][orm-project].

- Requires [doctrine/orm]

## Domain Repository

A Doctrine tailored [repository trait](../ddd/repositories.md) is provided by `MsgPhp\Domain\Infrastructure\Doctrine\DomainEntityRepositoryTrait`.

### Basic Example

```php
<?php

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainEntityRepositoryTrait;

// SETUP

/**
 * @ORM\Entity()
 */
class MyEntity
{
    /** @ORM\Id() @ORM\Column(type="string") */
    public $name;

    /** @ORM\Id() @ORM\Column(type="integer") */
    public $year;
}


class MyEntityRepository
{
    use DomainEntityRepositoryTrait {
        doFind as public find;
        doExists as public exists;
        doSave as public save;
    }
}

/** @var EntityManagerInterface $em */

$repository = new MyEntityRepository(MyEntity::class, $em);

// USAGE

if ($repository->exists($id = ['name' => '...', 'year' => date('Y')])) {
    $entity = $repository->find($id);
} else {
    $entity = new MyEntity();
    $entity->name = '...';
    $entity->year = date('Y');

    $repository->save($entity);
}
```

## Domain Object Factory

A Doctrine tailored [object factory](../ddd/object-factory.md) is provided by
`MsgPhp\Domain\Infrastructure\Doctrine\DomainObjectFactory`.

When working with [ORM inheritance] the discriminator field can be provided to factorize a specific entity type.

### Basic Example

```php
<?php

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use MsgPhp\Domain\Factory\DomainObjectFactory as BaseDomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainObjectFactory;

// SETUP

/**
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discriminator", type="string")
 * @ORM\DiscriminatorMap({"self" = "MyEntity", "other" = "MyOtherEntity"})
 */
class MyEntity
{
    public const TYPE_SELF = 'self';
    public const TYPE_OTHER = 'other';

    /** @ORM\Id() @ORM\Column(type="integer") */
    public $id;
}

/**
 * @ORM\Entity()
 */
class MyOtherEntity extends MyEntity
{
}

/** @var EntityManagerInterface $em */

$factory = new DomainObjectFactory(new BaseDomainObjectFactory(), $em);

// USAGE

/** @var MyOtherEntity $otherEntity */
$otherEntity = $factory->create(MyEntity::class, [
    'discriminator' => MyEntity::TYPE_OTHER,
]);
```

[orm-project]: http://www.doctrine-project.org/projects/orm.html
[ORM inheritance]: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html
[doctrine/orm]: https://packagist.org/packages/doctrine/orm
