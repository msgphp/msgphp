<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Exception;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EntityNotFoundException extends \RuntimeException implements DomainExceptionInterface
{
    public static function createForId(string $class, $id): self
    {
        return new self(sprintf('Entity "%s" with identifier %s cannot be found.', $class, json_encode($id)));
    }

    public static function createForFields(string $class, array $fields): self
    {
        return new self(sprintf('Entity "%s" with fields matching %s cannot be found.', $class, json_encode($fields)));
    }
}
