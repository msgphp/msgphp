# Doctrine Object Relational Mapper

An overview of available infrastructural code when using Doctrine's [Object Relational Mapper][orm-project].

- Requires [doctrine/orm]

## Domain identity mapping

A Doctrine tailored [domain identity mapping](../ddd/identity-mapping.md) is provided by
`MsgPhp\Domain\Infra\Doctrine\DomainIdentityMapping`. It uses Doctrine's [`EntityManagerInterface`][api-em] to provide
the identity mapping from its class metadata.

- `__construct(EntityManagerInterface $em, array $classMapping = [])`
    - `$em`: The entity manager to use
    - `$classMapping`: The class mapping (`['SourceType' => 'TargetType']`)

### Basic example

```php
<?php

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use MsgPhp\Domain\Infra\Doctrine\DomainIdentityMapping;

// --- SETUP ---

/** @ORM\Entity */
class MyCompositeEntity
{
    /** @ORM\Id @ORM\Column(type="string") */
    public $name;

    /** @ORM\Id @ORM\Column(type="integer") */
    public $year;
}

/** @var EntityManagerInterface $em */
$em = ...;
$mapping = new DomainIdentityMapping($em);
```

## Domain repository

A Doctrine tailored [repository trait](../ddd/repositories.md) is provided by
`MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait`. It uses Doctrine's [`EntityManagerInterface`][api-em] as
underlying persistence layer.

- `__construct(string $class, EntityManagerInterface $em, DomainIdentityHelper $identityHelper = null)`
    - `$class`: The entity class this repository is tied to
    - `$em`: The entity manager to use
    - `$identityHelper`: Custom domain identity helper. By default it's resolved from the given entity manager.
      [Read more](../ddd/identities.md).

### Basic example

```php
<?php

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;

// --- SETUP ---

/** @ORM\Entity */
class MyCompositeEntity
{
    /** @ORM\Id @ORM\Column(type="string") */
    public $name;

    /** @ORM\Id @ORM\Column(type="integer") */
    public $year;
}


class MyCompositeEntityRepository
{
    use DomainEntityRepositoryTrait {
        doFind as public find;
        doExists as public exists;
        doSave as public save;
    }
}

/** @var EntityManagerInterface $em */
$em = ...;
$repository = new MyCompositeEntityRepository(MyCompositeEntity::class, $em);

// --- USAGE ---

if ($repository->exists($id = ['name' => ..., 'year' => ...])) {
    $entity = $repository->find($id);
} else {
    $entity = new MyCompositeEntity();
    $entity->name = ...;
    $entity->year = ...;

    $repository->save($entity);
}
```

## Entity aware factory

A Doctrine tailored [entity aware factory](../ddd/factory/entity-aware.md) is provided by
`MsgPhp\Domain\Infra\Doctrine\EntityAwareFactory`. It decorates any entity aware factory and uses Doctrine's
[`EntityManagerInterface`][api-em]. Its purpose is to create lazy-loading references when using `reference()` (see 
[`EntityManagerInterface::getReference()`][api-em-getreference]) and handle an entity its discriminator map when working
with [inheritance][orm-inheritance].

- `__construct(EntityAwareFactoryInterface $factory, EntityManagerInterface $em, array $classMapping = [])`
    - `$factory`: The decorated factory
    - `$em`: The entity manager to use
    - `$classMapping`: The class mapping (`['SourceType' => 'TargetType']`)

### Basic example

```php
<?php

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use MsgPhp\Domain\Factory\{DomainObjectFactory, EntityAwareFactory as BaseEntityAwareFactory};
use MsgPhp\Domain\Infra\Doctrine\{DomainIdentityMapping, EntityAwareFactory};

// --- SETUP ---

/**
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discriminator", type="string")
 * @ORM\DiscriminatorMap({"self" = "MyEntity", "other" = "MyOtherEntity"})
 */
class MyEntity
{
    public const TYPE_SELF = 'self';
    public const TYPE_OTHER = 'other';

    /** @ORM\Id */
    public $id;
}

/** @ORM\Entity */
class MyOtherEntity extends MyEntity
{
}

/** @var EntityManagerInterface $em */
$em = ...;
$factory = new EntityAwareFactory(
    new BaseEntityAwareFactory(new DomainObjectFactory(), new DomainIdentityMapping($em)),
    $em
);

// --- USAGE ---

/** @var MyEntity $ref */
$ref = $factory->reference(MyEntity::class, 1); // no database hit

/** @var MyOtherEntity $ref */
$otherRef = $factory->reference(MyEntity::class, ['id' => 1, 'discriminator' => MyEntity::TYPE_OTHER]);

/** @var MyOtherEntity $otherEntity */
$otherEntity = $factory->create(MyEntity::class, [
    'discriminator' => MyEntity::TYPE_OTHER,
]);
```

## Hydration

When working with [identifiers](../ddd/identifiers.md) and the corresponding [type](doctrine-dbal.md#domain-identifier-type)
a problem might occur when hydrating scalar values, e.g. using [`Query::getScalarResult()`][api-query-getscalarresult];
it would return instances of `MsgPhp\Domain\DomainIdInterface` that can only be casted to string as its (true) scalar
value (due to `__toString()`). In case the underlying data type is e.g. `integer` we'll lose it.

To overcome, two hydration modes are available in order to hydrate the primitive identifier value instead.

### Basic example

```php
<?php

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Infra\Doctrine\DomainIdType;
use MsgPhp\Domain\Infra\Doctrine\Hydration\{ScalarHydrator, SingleScalarHydrator}

// --- SETUP ---

/** @ORM\Entity */
class MyEntity
{
    /** @var DomainId @ORM\Id @ORM\Column(type="msgphp_domain_id") */
    public $id;
}

Type::addType(DomainIdType::NAME, DomainIdType::class);

/** @var EntityManagerInterface $em */
$em = ...;
$em->getConfiguration()->addCustomHydrationMode(ScalarHydrator::NAME, ScalarHydrator::class);
$em->getConfiguration()->addCustomHydrationMode(SingleScalarHydrator::NAME, SingleScalarHydrator::class);

// --- USAGE ---

$query = $em->createQuery('SELECT entity.id FORM MyEntity entity');

$query->getScalarResult()[0]['id']; // "1"
$query->getResult(ScalarHydrator::NAME)[0]['id']; // int(1)

$query->getSingleScalarResult(); // "1"
$query->getSingleResult(SingleScalarHydrator::NAME); // int(1)
```

[orm-project]: http://www.doctrine-project.org/projects/orm.html
[orm-inheritance]: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html
[doctrine/orm]: https://packagist.org/packages/doctrine/orm
[api-em]: http://www.doctrine-project.org/api/orm/2.5/class-Doctrine.ORM.EntityManagerInterface.html
[api-em-getreference]: http://www.doctrine-project.org/api/orm/2.5/class-Doctrine.ORM.EntityManagerInterface.html#_getReference
[api-query-getscalarresult]: http://www.doctrine-project.org/api/orm/2.5/class-Doctrine.ORM.AbstractQuery.html#_getScalarResult
