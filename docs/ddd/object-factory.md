# Object Factory

A domain object factory is bound to `MsgPhp\Domain\Factory\DomainObjectFactory`. Its purpose is to initialize any domain
object based on a given class name and context.

## API

### `create(string $class, array $context = []): object`

Returns a factorized domain object by class name. Optionally a context can be provided for the factory to act upon.

### `reference(string $class, array $context = []): object`

Returns a factorized domain reference object by class name. Optionally a context can be provided for the factory to act
upon.

!!! info
    Factorizing a reference should not trigger its [constructor] to be called, nor trigger any form of external loading

### `getClass(string $class, array $context = []): string`

Returns the actual class name the factory uses for a given class name.

## Implementations

### `MsgPhp\Domain\Factory\GenericDomainObjectFactory`

A generic object factory. It initializes a class by reading its [constructor] arguments.

Context elements mapped by argument name will be used as argument value. In case of a type-hinted object argument a
nested context may be provided to initialize the object with.

To map interfaces and abstract classes to concrete classes a global class mapping can be provided.

#### Basic example

```php
<?php

use MsgPhp\Domain\Factory\GenericDomainObjectFactory;

// SETUP

interface Known
{
}

class Some implements Known
{
    public function __construct(int $a, ?int $b, ?int $c)
    {
    }
}

class Subject
{
    public function __construct(string $argument, Known $some, Subject $otherSubject = null)
    {
    }
}

$factory = new GenericDomainObjectFactory([
    Known::class => Some::class,
]);

// Optionally set the factory to use for nested objects, or use the current factory by default.
// $factory->setNestedFactory(...);

// USAGE

/** @var Some $object */
$object = $factory->create(Known::class, ['a' => 1]);
$factory->getClass(Known::class); // "Some"

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
    `GenericDomainObjectFactory::reference()` requires [symfony/var-exporter]

### Infrastructural

- [Doctrine ORM](../infrastructure/doctrine-orm.md#domain-object-factory)

[constructor]: https://secure.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor
[symfony/var-exporter]: https://packagist.org/packages/symfony/var-exporter
