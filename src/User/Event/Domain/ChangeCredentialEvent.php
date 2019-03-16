<?php

declare(strict_types=1);

namespace MsgPhp\User\Event\Domain;

use MsgPhp\Domain\Event\DomainEventInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class ChangeCredentialEvent implements DomainEventInterface
{
    /**
     * @var array
     */
    public $fields;

    final public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function getStringField(string $field): string
    {
        if (!isset($this->fields[$field])) {
            throw new \LogicException(sprintf('Field "%s" does not exists.', $field));
        }

        if (!\is_string($this->fields[$field])) {
            throw new \LogicException(sprintf('Field "%s" must be a string, got "%s".', $field, \gettype($this->fields[$field])));
        }

        return $this->fields[$field];
    }
}
