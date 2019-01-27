<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class AddUserRoleCommand
{
    public $userId;
    public $roleName;
    public $context;

    final public function __construct(UserIdInterface $userId, string $roleName, array $context = [])
    {
        $this->userId = $userId;
        $this->roleName = $roleName;
        $this->context = $context;
    }
}
