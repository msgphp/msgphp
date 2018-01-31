# Object factory

A domain object factory is bound to `MsgPhp\Domain\Factory\DomainObjectFactoryInterface`. Its purpose is to initialize
any domain object based on a class name and context.

## Implementations

### `MsgPhp\Domain\Factory\ChainObjectFactory`

Holds many object factories. It returns a domain object from the first supporting factory.

### `MsgPhp\Domain\Factory\ClassMappingObjectFactory`

Decorates any object factory. It resolves the actual class name to use from a provided mapping or, if unknown, it uses
the original provided class name.

### `MsgPhp\Domain\Factory\DomainObjectFactory`

Generic object factory. Initializes the given class name by reading its constructor arguments. Argument values are
resolved from the provided context. By convention a camel cased argument name (e.g. `$myArgument`) matches a
corresponding underscored context key (e.g. `['my_argument' => 'value']`). If the context key is numeric its value will
be provided to a corresponding argument at index N. In case an exact match exists (e.g. `['myArgument' => 'value']`) it
will always be used instead.

Any sub class of `MsgPhp\Domain\DomainIdInterface` or `MsgPhp\Domain\DomainCollectionInterface` will be initialized
from `$class::fromValue()` by default, otherwise initialization happens regulary (i.e. `new $class(...$args)`).

Nested objects (e.g. `MyObject $myArgument`) might be provided as nested context (thus array). The current factory will
be used to initialize the object as argument value. Another (decorating) factory can be set using 
`DomainObjectFactory::setNestedFactory(DomainObjectFactoryInterface $factory)`.

## API

### `create(string $class, array $context = []): object`

Factorizes a new domain object by class name. Optionally a context can be provided for the factory to act upon.

## Chain example

```php
<?php

use MsgPhp\Domain\Factory\ChainObjectFactory;

$firstFactory = ...;
$secondFactory = ...;

$factory = new ChainObjectFactory([$firstFactory, $secondFactory]);
$object = $factory->create(SomeObject::class, ['key' => 'value']);
```

## Class mapping example

```php
<?php

use MsgPhp\Domain\Factory\ClassMappingObjectFactory;

interface KnownInterface { }
class Subject implements KnownInterface { }

$realFactory = ...;

$factory = new ClassMappingObjectFactory([KnownInterface::class => Subject::class], $realFactory);

/** @var Subject $object */
$object = $factory->create(KnownInterface::class);
```

## Generic example

```php
<?php

use MsgPhp\Domain\Factory\DomainObjectFactory;

class Some
{
    public function __construct(int $a, ?int $b, ?int $c)
    { }
}

class Subject
{
    public function __construct(string $argument, Some $some, Subject $otherSubject = null)
    { }
}

$factory = new DomainObjectFactory();

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
