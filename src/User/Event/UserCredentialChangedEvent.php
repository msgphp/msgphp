<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\CredentialInterface;
use MsgPhp\User\Entity\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class UserCredentialChangedEvent
{
    public $user;
    public $oldCredential;
    public $newCredential;

    public function __construct(User $user, CredentialInterface $oldCredential, CredentialInterface $newCredential)
    {
        $this->user = $user;
        $this->oldCredential = $oldCredential;
        $this->newCredential = $newCredential;
    }
}
