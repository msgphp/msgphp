<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\ContextBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface ContextElementProviderInterface
{
    public function getElement(string $class, string $method, string $argument): ?ContextElement;
}
