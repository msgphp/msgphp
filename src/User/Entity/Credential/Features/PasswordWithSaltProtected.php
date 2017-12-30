<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Credential\Features;

use MsgPhp\User\Password\PasswordWithSaltProtectedTrait;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait PasswordWithSaltProtected
{
    use PasswordWithSaltProtectedTrait;

    abstract public function withPassword(string $password): self;

    abstract public function withPasswordSalt(string $passwordSalt): self;
}
