<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\Role;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class RoleDeletedEvent
{
    /**
     * @var Role
     */
    public $role;

    final public function __construct(Role $role)
    {
        $this->role = $role;
    }
}
