<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\Context;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface ClassContextElementFactoryInterface
{
    public function getElement(string $class, string $method, string $argument): ContextElement;
}
