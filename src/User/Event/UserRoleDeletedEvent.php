<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\UserRole;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class UserRoleDeletedEvent
{
    /**
     * @var UserRole
     */
    public $userRole;

    final public function __construct(UserRole $userRole)
    {
        $this->userRole = $userRole;
    }
}
