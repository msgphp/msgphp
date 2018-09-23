<?php

declare(strict_types=1);

namespace MsgPhp\User\Role;

use MsgPhp\User\Entity\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DefaultRoleProvider implements RoleProviderInterface
{
    private $roles;

    /**
     * @param string[] $roles
     */
    public function __construct(array $roles, bool $sanitize = true)
    {
        $this->roles = $sanitize ? array_values(array_unique($roles)) : $roles;
    }

    public function getRoles(User $user): array
    {
        return $this->roles;
    }
}
