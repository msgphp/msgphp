# Symfony Console

An overview of available infrastructural code when using [Symfony Console][console-project].

- Requires [symfony/console]

## Context factory

A context factory is bound to `MsgPhp\Domain\Infra\Console\Context\ContextFactoryInterface`. Its purpose is to leverage
a CLI command in an effort to interactively built an arbitrary array value (the context).

### API

#### `configure(InputDefinition $definition): void`

Configure a command input definition. See also [`InputDefinition`][api-inputdefinition]. Should be called before using
`getContext()`.

---

#### `getContext(InputInterface $input, StyleInterface $io): array`

Resolve the actual context from the console IO. See also [`InputInterface`][api-inputinterface] and [`StyleInterface`][api-styleinterface].

### Implementations

#### `MsgPhp\Domain\Infra\Console\Context\ClassContextFactory`

Factorizes a context based on any class method signature. It configures the CLI signature by mapping required class
method arguments to command arguments, whereas optional ones are mapped to command options.

```bash
bin/console command --optional-argument [--] required-argument
```

In both cases a value is optional, if the actual class method argument is required and no value is given it will be
asked interactively. If interaction is not possible an exception will be thrown instead.

- `__construct(string $class, string $method, iterable $elementProviders = [], array $classMapping = [], int $flags = 0)`
    - `$class / $method`: The class method to resolve
    - `$elementProviders`: Available context element providers (see [Providing context elements](#providing-context-elements))
    - `$classMapping`: Global class mapping. Usually used to map abstracts to concretes.
    - `$flags`: A bit mask value to toggle various flags
        - `ClassContextBuilder::ALWAYS_OPTIONAL`: Always map class method arguments to command options
        - `ClassContextBuilder::NO_DEFAULTS`: Leave out default values when calling `getContext()`

##### Providing context elements

Per-element configuration can be provided by implementing a `MsgPhp\Domain\Infra\Console\Context\ContextElementProviderInterface`.

- `getElement(string $class, string $method, string $argument): ?ContextElement`
    - Resolve a [`ContextElement`][api-contextelement] from a class/method/argument combination

##### Basic example

```php
<?php

use MsgPhp\Domain\Infra\Console\Context\{ClassContextBuilder, ContextElement, ContextElementProviderInterface};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// --- SETUP ---

class MyClass
{
    public function __construct(string $argument)
    {
    }
}

class MyContextElementProvider implements ContextElementProviderInterface
{
    public function getElement(string $class, string $method, string $argument): ?ContextElement
    {
        return new ContextElement(strtoupper($argument));
    }
}

class MyCommand extends Command
{
    private $contextBuilder;

    public function __construct()
    {
        $this->contextBuilder = new ClassContextBuilder(MyClass::class, '__construct', [new MyContextElementProvider()]);

        parent::__construct();
    }

    protected function configure(): void
    {
       $this->setName('my-command');
       $this->contextBuilder->configure($this->getDefinition());
    }

    protected function execute(InputInterface $input,OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $context = $this->contextBuilder->getContext($input, $io);
        $object = new MyClass(...array_values($context));

        // do something

        return 0;
    }
}

// --- USAGE ---

// $ bin/console my-command
```

[console-project]: https://symfony.com/doc/current/components/console.html
[symfony/console]: https://packagist.org/packages/symfony/console
[api-inputdefinition]: https://api.symfony.com/master/Symfony/Component/Console/Input/InputDefinition.html
[api-inputinterface]: https://api.symfony.com/master/Symfony/Component/Console/Input/InputInterface.html
[api-styleinterface]: https://api.symfony.com/master/Symfony/Component/Console/Style/StyleInterface.html
[api-contextelement]: https://msgphp.github.io/api/MsgPhp/Domain/Infra/Console/ContextBuilder/ContextElement.html
