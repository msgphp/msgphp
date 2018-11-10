# Object Factory

A domain object factory is bound to `MsgPhp\Domain\Factory\DomainObjectFactoryInterface`. Its purpose is to initialize
any domain object based on a given class name and context.

## API

### `create(string $class, array $context = []): object`

Returns a factorized domain object by class name. Optionally a context can be provided for the factory to act upon.

### `getClass(string $class, array $context = []): string`

Returns the actual class name the factory will create and equalizes `get_class($factory->create($class, $context))`.

## Implementations

### `MsgPhp\Domain\Factory\DomainObjectFactory`

A generic object factory. It initializes the given class by reading its constructor arguments. Argument values are
resolved from the provided context. By convention a camel cased argument name (e.g. `$myArgument`) matches a
corresponding underscored context key (i.e. `'my_argument'`), note however, an exact match (i.e. `'myArgument'`) has
higher precedence. In case the context key is numeric its value will be provided to a corresponding argument at index N.

Any sub class of `MsgPhp\Domain\DomainIdInterface` or `MsgPhp\Domain\DomainCollectionInterface` will be initialized
using `$class::fromValue()` by default, otherwise initialization happens regularly (i.e. `new $class(...$arguments)`).

A class mapping can be provided and is usually used to map abstracts to concretes.

Nested objects (e.g. `MyObject $myArgument`) can be provided as nested context (thus nested array).

- `setNestedFactory(?DomainObjectFactoryInterface $factory): void`
    - `$classMapping`: The class mapping (`['SourceType' => 'TargetType']`)`
    - `$factory`: The optional factory to use for nested objects. If not set the current factory will be used instead.

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
    'some' => [1, 2, 3],
    'other_subject' => [
        'argument' => 'other_value',
        'some' => ['a' => 1],
    ],
]);
```
