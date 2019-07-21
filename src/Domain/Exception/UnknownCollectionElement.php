<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Exception;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UnknownCollectionElement extends \OutOfBoundsException implements DomainException
{
    /**
     * @param string|int $key
     */
    public static function createForKey($key): self
    {
        return new self('Collection element with key "'.$key.'" does not exists.');
    }
}
