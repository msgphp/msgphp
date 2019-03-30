<?php

declare(strict_types=1);

namespace MsgPhp\User;

use MsgPhp\User\Credential\Anonymous;
use MsgPhp\User\Credential\Credential;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class User
{
    abstract public function getId(): UserId;

    public function getCredential(): Credential
    {
        return new Anonymous();
    }
}
