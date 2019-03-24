<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Exception;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EntityNotFoundException extends \RuntimeException implements DomainException
{
    /**
     * @param mixed $id
     */
    public static function createForId(string $class, $id): self
    {
        return new self(sprintf('Entity "%s" with identity %s cannot be found.', $class, (string) json_encode($id)));
    }

    public static function createForFields(string $class, array $fields): self
    {
        return new self(sprintf('Entity "%s" with fields matching %s cannot be found.', $class, (string) json_encode($fields)));
    }
}
