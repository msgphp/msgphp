<?php

declare(strict_types=1);

namespace MsgPhp\User\Role;

use MsgPhp\User\Entity\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChainRoleProvider implements RoleProviderInterface
{
    /**
     * @var iterable|RoleProviderInterface[]
     */
    private $providers;

    /**
     * @param RoleProviderInterface[] $providers
     */
    public function __construct(iterable $providers)
    {
        $this->providers = $providers;
    }

    public function getRoles(User $user): array
    {
        $roles = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->getRoles($user) as $role) {
                $roles[$role] = true;
            }
        }

        return array_keys($roles);
    }
}
