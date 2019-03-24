<?php

declare(strict_types=1);

namespace MsgPhp\User\Event\Domain;

use MsgPhp\Domain\Event\DomainEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class RequestPassword implements DomainEvent
{
    /**
     * @var string|null
     */
    public $token;

    final public function __construct(string $token = null)
    {
        $this->token = $token;
    }
}
