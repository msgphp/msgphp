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

// --- SETUP ---

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
$em = ...;
$repository = new MyEntityRepository(MyEntity::class, $em);

// --- USAGE ---

if ($repository->exists($id = ['name' => ..., 'year' => ...])) {
    $entity = $repository->find($id);
} else {
    $entity = new MyEntity();
    $entity->name = ...;
    $entity->year = ...;

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

// --- SETUP ---

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
$em = ...;
$factory = new DomainObjectFactory(new BaseDomainObjectFactory(), $em);

// --- USAGE ---

/** @var MyOtherEntity $otherEntity */
$otherEntity = $factory->create(MyEntity::class, [
    'discriminator' => MyEntity::TYPE_OTHER,
]);
```

## Domain Identifier Hydration

When working with [domain identifiers](../ddd/identifiers.md) and its corresponding [type](doctrine-dbal.md#domain-identifier-type)
a problem can occur when hydrating scalar values, e.g. with `Query::getScalarResult()`.

It would use instances of `MsgPhp\Domain\DomainId` that can only be casted to string as its (true) scalar value (due to
`__toString()`). In case the underlying data type is e.g. `integer` it will be lost.

To overcome, two hydration modes are available to hydrate the primitive identifier value instead.

### Basic Example

```php
<?php

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use MsgPhp\Domain\Infrastructure\Doctrine\Hydration\ScalarHydrator;
use MsgPhp\Domain\Infrastructure\Doctrine\Hydration\SingleScalarHydrator;

// --- SETUP ---

/**
 * @ORM\Entity()
 */
class MyEntity
{
    /**
     * @var MyDomainId|null
     * @ORM\Id() @ORM\Column(type="my_domain_id")
     */
    public $id;
}

MyDomainIdType::setClass(MyDomainId::class);
MyDomainIdType::setDataType(Type::INTEGER);
Type::addType(MyDomainIdType::NAME, MyDomainIdType::class);

/** @var EntityManagerInterface $em */
$em = ...;
$config = $em->getConfiguration();

$config->addCustomHydrationMode(ScalarHydrator::NAME, ScalarHydrator::class);
$config->addCustomHydrationMode(SingleScalarHydrator::NAME, SingleScalarHydrator::class);

// --- USAGE ---

$query = $em->createQuery('SELECT entity.id FROM MyEntity entity');

$query->getScalarResult()[0]['id']; // "1"
$query->getResult(ScalarHydrator::NAME)[0]['id']; // int(1)

$query->getSingleScalarResult(); // "1"
$query->getSingleResult(SingleScalarHydrator::NAME); // int(1)
```

[orm-project]: http://www.doctrine-project.org/projects/orm.html
[ORM inheritance]: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html
[doctrine/orm]: https://packagist.org/packages/doctrine/orm
