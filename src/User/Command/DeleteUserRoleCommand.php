<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserRoleCommand
{
    public $userId;
    public $role;

    public function __construct(UserIdInterface $userId, string $role)
    {
        $this->userId = $userId;
        $this->role = $role;
    }
}
