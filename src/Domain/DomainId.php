<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DomainId
{
    /**
     * @internal
     */
    public function __toString(): string;

    /**
     * @param mixed $value
     *
     * @return static
     */
    public static function fromValue($value): self;

    public function isNil(): bool;

    /**
     * @param mixed $other
     */
    public function equals($other): bool;

    public function toString(): string;
}
