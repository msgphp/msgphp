<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Credential\Features;

use MsgPhp\User\Password\PasswordProtectedTrait;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait PasswordProtected
{
    use PasswordProtectedTrait;

    abstract public function withPassword(string $password): self;
}
