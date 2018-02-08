<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DeleteUserRoleCommand
{
    public $userId;
    public $roleName;

    final public function __construct($userId, string $roleName)
    {
        $this->userId = $userId;
        $this->roleName = $roleName;
    }
}
