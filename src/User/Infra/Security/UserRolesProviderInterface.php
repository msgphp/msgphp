<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\User\Entity\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UserRolesProviderInterface
{
    /**
     * @return string[]
     */
    public function getRoles(User $user): array;
}
