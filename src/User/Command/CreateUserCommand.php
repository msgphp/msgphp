<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreateUserCommand
{
    public $userId;
    public $email;
    public $password;
    public $enable;
    public $plainPassword;
    public $context;

    public function __construct(?UserIdInterface $userId, string $email, string $password, bool $enable = false, bool $plainPassword = true, array $context = [])
    {
        $this->userId = $userId;
        $this->email = $email;
        $this->password = $password;
        $this->enable = $enable;
        $this->plainPassword = $plainPassword;
        $this->context = $context;
    }
}
