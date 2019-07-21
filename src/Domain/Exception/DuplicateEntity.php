<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Exception;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DuplicateEntity extends \RuntimeException implements DomainException
{
    /**
     * @param mixed $id
     */
    public static function createForId(string $class, $id): self
    {
        return new self('Entity "'.$class.'" with identity '.json_encode($id).' cannot be duplicated.');
    }
}
