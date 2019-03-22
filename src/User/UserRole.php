<?php

declare(strict_types=1);

namespace MsgPhp\User;

use MsgPhp\User\Model\RoleField;
use MsgPhp\User\Model\UserField;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
abstract class UserRole
{
    use UserField;
    use RoleField;

    public function __construct(User $user, Role $role)
    {
        $this->user = $user;
        $this->role = $role;
    }
}
