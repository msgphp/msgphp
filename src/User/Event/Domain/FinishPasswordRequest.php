<?php

declare(strict_types=1);

namespace MsgPhp\User\Event\Domain;

use MsgPhp\Domain\Event\DomainEvent;
use MsgPhp\User\Credential\Credential;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class FinishPasswordRequest implements DomainEvent
{
    /**
     * @var Credential
     */
    public $oldCredential;

    public function __construct(Credential $oldCredential)
    {
        $this->oldCredential = $oldCredential;
    }
}
