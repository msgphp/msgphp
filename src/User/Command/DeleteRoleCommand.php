<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DeleteRoleCommand
{
    public $roleName;

    final public function __construct(string $roleName)
    {
        $this->roleName = $roleName;
    }
}
