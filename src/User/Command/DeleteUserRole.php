<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DeleteUserRole
{
    /**
     * @var UserId
     */
    public $userId;

    /**
     * @var string
     */
    public $roleName;

    final public function __construct(UserId $userId, string $roleName)
    {
        $this->userId = $userId;
        $this->roleName = $roleName;
    }
}
