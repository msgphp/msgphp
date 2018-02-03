<?php

declare(strict_types=1);

namespace MsgPhp\User\Event\Domain;

use MsgPhp\Domain\Event\DomainEventInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class ChangeCredentialEvent implements DomainEventInterface
{
    public $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }
}
