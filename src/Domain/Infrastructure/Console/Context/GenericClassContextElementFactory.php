<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Console\Context;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class GenericClassContextElementFactory implements ClassContextElementFactory
{
    /**
     * @inheritdoc
     */
    public function getElement(string $class, string $method, string $argument): ContextElement
    {
        return new ContextElement(ucfirst((string) preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1 \\2', '\\1 \\2'], $argument)));
    }
}
