<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChangeUserPasswordCommand
{
    public $userId;
    public $password;

    public function __construct(UserIdInterface $userId, string $password)
    {
        $this->userId = $userId;
        $this->password = $password;
    }
}
