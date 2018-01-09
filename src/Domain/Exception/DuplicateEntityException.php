<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Exception;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DuplicateEntityException extends \RuntimeException implements DomainExceptionInterface
{
    public static function createForId(string $entity, $id, ...$idN): self
    {
        if ($idN) {
            $id = func_get_args();
            array_shift($id);
        }

        return new self(sprintf('Entity "%s" with identifier %s cannot be duplicated.', $entity, json_encode($id)));
    }
}
