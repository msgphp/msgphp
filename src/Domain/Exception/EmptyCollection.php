<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Exception;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EmptyCollection extends \RuntimeException implements DomainException
{
    public static function create(): self
    {
        return new self('Cannot obtain elements from an empty collection.');
    }
}
