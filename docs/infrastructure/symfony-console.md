# Symfony Console

An overview of available infrastructural code when using [Symfony Console][console-project].

- Requires [symfony/console]

## Commands

Various standard [console commands] are available and can be used depending on implemented domain infrastructure. They
are defined in the `MsgPhp\Domain\Infra\Console\Command\` namespace.

### `InitializeProjectionTypesCommand`

Initializes the [projection type registry](../projection/type-registry.md).

```bash
bin/console projection:initialize-types [--force]
```

### `SynchronizeProjectionsCommand`

Synchronizes domain objects and their [projections](../projection/models.md) using the [projection synchronization](../projection/synchronization.md)
utility service.

```bash
bin/console projection:synchronize
```

## Context Factory

A context factory is bound to `MsgPhp\Domain\Infra\Console\Context\ContextFactoryInterface`. Its purpose is to leverage
a CLI command in an effort to interactively built an arbitrary array value (the context).

### API

#### `configure(InputDefinition $definition): void`

Configures a command input definition. See also [`InputDefinition`][api-inputdefinition]. Should be called before using
`getContext()`.

---

#### `getContext(InputInterface $input, StyleInterface $io, array $values = []): array`

Resolves the actual context from the console IO. See also [`InputInterface`][api-inputinterface] and [`StyleInterface`][api-styleinterface].
Any element value provided by `$values` takes precedence and should be used as-is.

### Implementations

#### `MsgPhp\Domain\Infra\Console\Context\ClassContextFactory`

Factorizes a context based on any class method signature. It configures the CLI signature by mapping required class
method arguments to command arguments, whereas optional ones are mapped to command options.

```bash
bin/console command --optional-argument [--] required-argument
```

In both cases a value is optional, if the actual class method argument is required and no value is given it will be
asked interactively. If interaction is not possible an exception will be thrown instead.

- `__construct(string $class, string $method, array $classMapping = [], int $flags = 0, ClassContextElementFactoryInterface $elementFactory = null)`
    - `$class / $method`: The class method to resolve
    - `$classMapping`: Global class mapping. Usually used to map abstracts to concretes.
    - `$flags`: A bit mask value to toggle various flags
        - `ClassContextBuilder::ALWAYS_OPTIONAL`: Always map class method arguments to command options
        - `ClassContextBuilder::NO_DEFAULTS`: Leave out default values when calling `getContext()`
        - `ClassContextBuilder::REUSE_DEFINITION`: Reuse the original input definition for matching class method
           arguments
    - `$elementFactory`: A custom element factory to use. See also [Customizing context elements](#customizing-context-elements).

##### Customizing context elements

Per-element configuration can be provided by implementing a `MsgPhp\Domain\Infra\Console\Context\ClassContextElementFactoryInterface`.

- `getElement(string $class, string $method, string $argument): ContextElement`
    - Get a custom [`ContextElement`][api-contextelement] to apply to a specific class/method/argument pair

A default implementation is provided by `MsgPhp\Domain\Infra\Console\Context\ClassContextElementFactory` which simply
transforms argument names to human readable values so that `$argumentName` becomes `Argument Name`.

##### Basic example

```php
<?php

use MsgPhp\Domain\Infra\Console\Context\ClassContextFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// --- SETUP ---

class MyObject
{
    public function __construct(string $argument, $option = null)
    {
    }
}

class MyCommand extends Command
{
    private $contextFactory;

    public function __construct()
    {
        $this->contextFactory = new ClassContextFactory(MyObject::class, '__construct');

        parent::__construct();
    }

    protected function configure(): void
    {
       $this->setName('my-command');
       $this->contextFactory->configure($this->getDefinition());
    }

    protected function execute(InputInterface $input,OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $context = $this->contextFactory->getContext($input, $io);
        $object = new MyObject(...array_values($context));

        // do something

        return 0;
    }
}

// --- USAGE ---

// $ bin/console my-command [--option=OPTION] [--] [<argument>]
```

#### `MsgPhp\Domain\Infra\Console\Context\DoctrineEntityContextFactory`

A [Doctrine](doctrine-orm.md) entity aware context factory. It decorates any context factory. Its purpose is to
provide a discriminator value into the resulting context when working with [inheritance][orm-inheritance].

- `__construct(ContextFactoryInterface $factory, EntityManagerInterface $em, string $class)`
    - `$factory`: The decorated context factory
    - `$em`: The entity manager to use
    - `$class`: The entity class to use

[console-project]: https://symfony.com/doc/current/components/console.html
[symfony/console]: https://packagist.org/packages/symfony/console
[console commands]: https://symfony.com/doc/current/console.html
[api-inputdefinition]: https://api.symfony.com/master/Symfony/Component/Console/Input/InputDefinition.html
[api-inputinterface]: https://api.symfony.com/master/Symfony/Component/Console/Input/InputInterface.html
[api-styleinterface]: https://api.symfony.com/master/Symfony/Component/Console/Style/StyleInterface.html
[api-contextelement]: https://msgphp.github.io/api/MsgPhp/Domain/Infra/Console/Context/ContextElement.html
[orm-inheritance]: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html
