<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\Context;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ClassContextElementFactory implements ClassContextElementFactoryInterface
{
    public function getElement(string $class, string $method, string $argument): ContextElement
    {
        return new ContextElement(ucfirst(preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1 \\2', '\\1 \\2'], $argument)));
    }
}
