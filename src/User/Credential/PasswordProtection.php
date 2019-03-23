<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

use MsgPhp\User\Password\PasswordAlgorithm;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait PasswordProtection
{
    /**
     * @var string
     */
    private $password;

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPasswordAlgorithm(): ?PasswordAlgorithm
    {
        return null;
    }
}
