<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Exception;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EmptyCollectionException extends \RuntimeException implements DomainExceptionInterface
{
    public static function create()
    {
        return new self('Cannot obtain element from an empty collection.');
    }
}
