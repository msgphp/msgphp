<?php

declare(strict_types=1);

namespace MsgPhp\User\Event\Domain;

use MsgPhp\Domain\Event\DomainEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class ChangeCredential implements DomainEvent
{
    /**
     * @var array
     */
    public $fields;

    final public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function getStringField(string $field, string $default = null): string
    {
        if (null === $value = $this->fields[$field] ?? $default) {
            throw new \LogicException(sprintf('No value available for field "%s".', $field));
        }

        if (!\is_string($value)) {
            throw new \LogicException(sprintf('Field value for "%s" must be a string, got "%s".', $field, \gettype($value)));
        }

        return $value;
    }
}
