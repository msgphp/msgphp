<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Exception;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class InvalidClass extends \LogicException implements DomainException
{
    public static function create(string $class): self
    {
        return new self('Invalid class "'.$class.'" detected.');
    }

    public static function createForMethod(string $class, string $method): self
    {
        return new self('Invalid class "'.$class.'" detected for method "'.$method.'".');
    }
}
