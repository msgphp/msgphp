# Object Factory

A domain object factory is bound to `MsgPhp\Domain\Factory\DomainObjectFactoryInterface`. Its purpose is to initialize
any domain object based on a given class name and context.

## API

### `create(string $class, array $context = []): object`

Returns a factorized domain object by class name. Optionally a context can be provided for the factory to act upon.

---

### `reference(string $class, $id): object`

Returns a factorized domain reference object by class name. Optionally a context can be provided for the factory to act
upon.

!!! info
    Factorizing a reference should not trigger its [constructor] to be called, nor trigger any form of external loading

---

### `getClass(string $class, array $context = []): string`

Returns the actual class name the factory uses for a given class name.

## Implementations

### `MsgPhp\Domain\Factory\DomainObjectFactory`

A generic object factory. It initializes a class by reading its [constructor] arguments. If the class is a sub class
of `MsgPhp\Domain\DomainIdInterface` or `MsgPhp\Domain\DomainCollectionInterface` its static `fromValue` constructor
will be used instead.

Context elements mapped by argument name will be used as argument value. In case of a type-hinted object argument a
nested context may be provided to initialize the object with.

To map interfaces and abstract classes to concrete classes a global class mapping can be provided.

- `__construct(array $classMapping = [])`
    - `$classMapping`: The class mapping (`['SourceType' => 'TargetType']`)`
- `setNestedFactory(?DomainObjectFactoryInterface $factory): void`
    - `$factory`: The factory to use for creating nested objects, or `null` to use the current instance

#### Basic example

```php
<?php

use MsgPhp\Domain\Factory\DomainObjectFactory;

// --- SETUP ---

interface KnownInterface
{
}

class Some implements KnownInterface
{
    public function __construct(int $a, ?int $b, ?int $c)
    {
    }
}

class Subject
{
    public function __construct(string $argument, KnownInterface $some, Subject $otherSubject = null)
    {
    }
}

$factory = new DomainObjectFactory([
    KnownInterface::class => Some::class,
]);

// --- USAGE ---

/** @var Some $object */
$object = $factory->create(KnownInterface::class, ['a' => 1]);
$factory->getClass(KnownInterface::class); // "Some"

/** @var Subject $object */
$object = $factory->create(Subject::class, [
    'argument' =>  'value',
    'some' => ['a' => 1, 'b' => 2],
    'otherSubject' => [
        'argument' => 'other value',
        'some' => ['a' => 1],
    ],
]);

/** @var Subject $object */
$object = $factory->reference(Subject::class);
```

!!! note
    `DomainObjectFactory::reference()` requires [symfony/var-exporter]

### `MsgPhp\Domain\Infra\Doctrine\DomainObjectFactory`

A Doctrine tailored object factory.

- [Read more](../infrastructure/doctrine-orm.md#domain-object-factory)

[constructor]: https://secure.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor
[symfony/var-exporter]: https://packagist.org/packages/symfony/var-exporter
