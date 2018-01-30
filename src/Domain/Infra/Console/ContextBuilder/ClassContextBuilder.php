<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\ContextBuilder;

use MsgPhp\Domain\Factory\ClassMethodResolver;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ClassContextBuilder extends AbstractContextBuilder
{
    private $class;
    private $method;
    private $resolved;

    public function __construct(string $class, string $method = '__construct')
    {
        $this->class = $class;
        $this->method = $method;
    }

    protected function getArguments(): iterable
    {
        $resolved = $this->resolve();

        return new \EmptyIterator();
    }

    protected function getOptions(): iterable
    {
        $resolved = $this->resolve();

        return new \EmptyIterator();
    }

    private function resolve(): array
    {
        return $this->resolved ?? ($this->resolved = ClassMethodResolver::resolve($this->class, $this->method));
    }
}
