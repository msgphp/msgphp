<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\CredentialInterface;
use MsgPhp\User\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class UserCredentialChangedEvent
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var CredentialInterface
     */
    public $oldCredential;

    /**
     * @var CredentialInterface
     */
    public $newCredential;

    final public function __construct(User $user, CredentialInterface $oldCredential, CredentialInterface $newCredential)
    {
        $this->user = $user;
        $this->oldCredential = $oldCredential;
        $this->newCredential = $newCredential;
    }
}
