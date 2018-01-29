<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DisableUserCommand
{
    public $userId;

    public function __construct(UserIdInterface $userId)
    {
        $this->userId = $userId;
    }
}
