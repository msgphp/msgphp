<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DeleteUserRoleCommand
{
    public $userId;
    public $roleName;

    final public function __construct(UserIdInterface $userId, string $roleName)
    {
        $this->userId = $userId;
        $this->roleName = $roleName;
    }
}
