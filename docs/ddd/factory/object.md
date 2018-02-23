# Object factory

A domain object factory is bound to `MsgPhp\Domain\Factory\DomainObjectFactoryInterface`. Its purpose is to initialize
any domain object based on a given class name and context.

## API

### `create(string $class, array $context = []): object`

Factorizes a new domain object by class name. Optionally a context can be provided for the factory to act upon.

## Implementations

### `MsgPhp\Domain\Factory\DomainObjectFactory`

A generic object factory. It initializes the given class by reading its constructor arguments. Argument values are
resolved from the provided context. By convention a camel cased argument name (e.g. `$myArgument`) matches a
corresponding underscored context key (i.e. `'my_argument'`), note however, an exact match (i.e. `'myArgument'`) has
higher precedence. In case the context key is numeric its value will be provided to a corresponding argument at index N.

Any sub class of `MsgPhp\Domain\DomainIdInterface` or `MsgPhp\Domain\DomainCollectionInterface` will be initialized
using `$class::fromValue()` by default, otherwise initialization happens regularly (i.e. `new $class(...$arguments)`).

Nested objects (e.g. `MyObject $myArgument`) can be provided as nested context (thus nested array).

- `setNestedFactory(?DomainObjectFactoryInterface $factory): void`
    - `$factory`: The optional factory to use for nested objects. If not set the current factory will be used instead.

#### Basic example

```php
<?php

use MsgPhp\Domain\Factory\DomainObjectFactory;

// --- SETUP ---

class Some
{
    public function __construct(int $a, ?int $b, ?int $c)
    {
    }
}

class Subject
{
    public function __construct(string $argument, Some $some, Subject $otherSubject = null)
    {
    }
}

$factory = new DomainObjectFactory();

// --- USAGE ---

/** @var Subject $object */
$object = $factory->create(Subject::class, [
    'argument' =>  'value',
    'some' => [1, 2, 3],
    'other_subject' => [
        'argument' => 'other_value',
        'some' => ['a' => 1],
    ],
]);
```

### `MsgPhp\Domain\Factory\ChainObjectFactory`

A chain object factory. It holds many object factories and returns a domain object from the first supporting factory.

- `__construct(iterable $factories)`
    - `$factories`: Available object factories

#### Basic example

```php
<?php

use MsgPhp\Domain\Factory\{ChainObjectFactory, DomainObjectFactory, DomainObjectFactoryInterface};

// --- SETUP ---

class MyFactory implements DomainObjectFactoryInterface
{
    public function create(string $class, array $context = [])
    {
        // ...
    }
}

$factory = new ChainObjectFactory([new MyFactory(), new DomainObjectFactory()]);
```

### `MsgPhp\Domain\Factory\ClassMappingObjectFactory`

A class mapping object factory. It decorates any object factory and resolves the actual class name from a provided
mapping. In case the class is not mapped it will be used as is.

- `__construct(DomainObjectFactoryInterface $factory, array $mapping)`
    - `$factory`: The decorated object factory
    - `$mapping`: The class mapping (`['SourceType' => 'TargetType']`)

#### Basic example

```php
<?php

use MsgPhp\Domain\Factory\{ClassMappingObjectFactory, DomainObjectFactory};

// --- SETUP ---

interface KnownInterface
{
}

class Subject implements KnownInterface
{
}

$factory = new ClassMappingObjectFactory(
    new DomainObjectFactory(),
    [KnownInterface::class => Subject::class]
);

// --- USAGE ---

/** @var Subject $object */
$object = $factory->create(KnownInterface::class);
```
