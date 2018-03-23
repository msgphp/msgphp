# PSR Container

An overview of available infrastructural code when using PSR [Containers][container-project], also known as PSR-11.

- Requires [psr/container]

## Domain projection document transformer

A PSR tailored [domain projection document transformer](../projection/document-transformers.md) is provided by `MsgPhp\Domain\Infra\Psr\DomainProjectionDocumentTransformer`.
It decorates any [`ContainerInterface`][api-container] and uses callable factories as container entries, identified by a
domain object class name.

- `__construct(ContainerInterface $container)`
    - `$container`: The decorated container

### Basic example

```php
<?php

use MsgPhp\Domain\Infra\Psr\DomainProjectionDocumentTransformer;
use Psr\Container\ContainerInterface;

// --- SETUP ---

class MyEntity
{
}

/** @var ContainerInterface $container */
$container = ...;
$transformer = new DomainProjectionDocumentTransformer($container);

// --- USAGE ---

$document = $transformer->transform(new MyEntity());
```

### Manual container example

In practice you often get a `$container` from configuration, e.g. when working with Symfony's [Service Locators]. This
example shows how to manually create a container using PHP7 anonymous classes.

```php
<?php

use MsgPhp\Domain\Projection\DomainProjectionDocument;
use MsgPhp\Domain\Projection\DomainProjectionInterface;
use MsgPhp\Domain\Infra\Psr\DomainProjectionDocumentTransformer;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

// --- SETUP ---

class MyEntity
{
    public $someField;
}

class MyProjection implements DomainProjectionInterface
{
    public static function fromDocument(array $document): DomainProjectionInterface
    {
        // ...
    }
}

$container = new class() implements ContainerInterface {
    public function has($id)
    {
        return MyEntity::class === $id;
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new class('Entry not found') extends \RuntimeException implements NotFoundExceptionInterface {
            };
        }

        return function (MyEntity $object) {
            $document = DomainProjectionDocument::create(MyProjection::class, null, [
                'some_field' => $object->someField,
            ]);
            $document->source = $object;

            return $document;
        };
    }
};
$transformer = new DomainProjectionDocumentTransformer($container);
```

[container-project]: https://www.php-fig.org/psr/psr-11/
[psr/container]: https://packagist.org/packages/psr/container
[api-container]: https://www.php-fig.org/psr/psr-11/#31-psrcontainercontainerinterface
[Service Locators]: https://symfony.com/doc/current/service_container/service_locators.html 
[anonymous classes]: https://secure.php.net/manual/en/language.oop5.anonymous.php
