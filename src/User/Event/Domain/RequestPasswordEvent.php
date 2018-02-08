<?php

declare(strict_types=1);

namespace MsgPhp\User\Event\Domain;

use MsgPhp\Domain\Event\DomainEventInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class RequestPasswordEvent implements DomainEventInterface
{
    public $token;

    public function __construct(string $token = null)
    {
        $this->token = $token;
    }
}
