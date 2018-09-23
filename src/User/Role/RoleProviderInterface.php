<?php

declare(strict_types=1);

namespace MsgPhp\User\Role;

use MsgPhp\User\Entity\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface RoleProviderInterface
{
    /**
     * @return string[]
     */
    public function getRoles(User $user): array;
}
