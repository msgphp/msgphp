<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Exception;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DuplicateEntityException extends \RuntimeException implements DomainExceptionInterface
{
    public static function createForId(string $class, $id): self
    {
        return new self(sprintf('Entity "%s" with identity %s cannot be duplicated.', $class, (string) json_encode($id)));
    }
}
