<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DomainObjectFactoryInterface
{
    /**
     * @return object
     */
    public function create(string $class, array $context = []);

    public function getClass(string $class, array $context = []): string;
}
