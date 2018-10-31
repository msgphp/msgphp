<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Exception;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UnknownCollectionElementException extends \OutOfBoundsException implements DomainExceptionInterface
{
    /**
     * @param string|int $key
     */
    public static function createForKey($key): self
    {
        return new self(sprintf('Collection element with key "%s" does not exists.', (string) $key));
    }
}
