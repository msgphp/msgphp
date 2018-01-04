<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\User\Entity\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @todo rename to UserRolesProviderInterface
 */
interface UserRoleProviderInterface
{
    /**
     * @return string[]
     */
    public function getRoles(User $user): array;
}
