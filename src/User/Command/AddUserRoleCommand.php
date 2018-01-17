<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserRoleCommand
{
    public $userId;
    public $role;
    public $context;

    public function __construct(UserIdInterface $userId, string $role, array $context = [])
    {
        $this->userId = $userId;
        $this->role = $role;
        $this->context = $context;
    }
}
