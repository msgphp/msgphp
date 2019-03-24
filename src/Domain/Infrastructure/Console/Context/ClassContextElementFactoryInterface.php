<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Console\Context;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface ClassContextElementFactoryInterface
{
    /**
     * @psalm-param class-string $class
     */
    public function getElement(string $class, string $method, string $argument): ContextElement;
}
