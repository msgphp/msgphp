<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Exception;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EntityNotFoundException extends \RuntimeException implements DomainExceptionInterface
{
    public static function createForFields(string $entity, array $fields): self
    {
        $fields = json_encode($fields);
        $type = 0 === strpos($fields, '[') ? 'primary fields' : 'fields';

        return new self(sprintf('Entity "%s" with %s matching %s cannot be found.', $entity, $type, $fields));
    }
}
