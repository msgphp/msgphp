<?php

declare(strict_types=1);

namespace MsgPhp\User\Credential;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait PasswordProtection
{
    /** @var string */
    private $password;

    public static function getPasswordField(): string
    {
        return 'password';
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
