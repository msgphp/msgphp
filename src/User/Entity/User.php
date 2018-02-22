<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity;

use MsgPhp\User\{CredentialInterface, UserIdInterface};
use MsgPhp\User\Entity\Credential\Anonymous;

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
