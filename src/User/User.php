<?php

declare(strict_types=1);

namespace MsgPhp\User;

use MsgPhp\User\Credential\Anonymous;
use MsgPhp\User\Credential\CredentialInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class User
{
    abstract public function getId(): UserIdInterface;

    /**
     * @return CredentialInterface
     */
    public function getCredential()
    {
        return new Anonymous();
    }
}
