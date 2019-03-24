<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DomainObjectFactory
{
    /**
     * @psalm-template T
     * @psalm-param class-string<T> $class
     * @psalm-return T
     *
     * @return object
     */
    public function create(string $class, array $context = []);

    /**
     * @psalm-template T
     * @psalm-param class-string<T> $class
     * @psalm-return T
     *
     * @return object
     */
    public function reference(string $class, array $context = []);

    /**
     * @psalm-param class-string $class
     * @psalm-return class-string
     */
    public function getClass(string $class, array $context = []): string;
}
