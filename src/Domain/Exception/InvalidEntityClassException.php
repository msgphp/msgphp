<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Exception;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class InvalidEntityClassException extends \LogicException implements DomainExceptionInterface
{
    public static function create(string $class): self
    {
        return new self(sprintf('Invalid entity class "%s" detected.', $class));
    }
}
