<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\Credential\Credential;
use MsgPhp\User\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class UserCredentialChanged
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var Credential
     */
    public $oldCredential;

    /**
     * @var Credential
     */
    public $newCredential;

    final public function __construct(User $user, Credential $oldCredential, Credential $newCredential)
    {
        $this->user = $user;
        $this->oldCredential = $oldCredential;
        $this->newCredential = $newCredential;
    }
}
