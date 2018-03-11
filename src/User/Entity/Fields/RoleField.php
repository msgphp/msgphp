<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Fields;

use MsgPhp\User\Entity\Role;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait RoleField
{
    /** @var Role */
    private $role;

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getRoleName(): string
    {
        return $this->role->getName();
    }
}
