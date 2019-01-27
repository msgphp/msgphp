<?php

declare(strict_types=1);

namespace MsgPhp\User\Role;

use MsgPhp\User\Entity\{User, UserRole};
use MsgPhp\User\Repository\UserRoleRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserRoleProvider implements RoleProviderInterface
{
    private $repository;

    public function __construct(UserRoleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getRoles(User $user): array
    {
        $roles = $this->repository->findAllByUserId($user->getId())->map(function (UserRole $userRole): string {
            return $userRole->getRoleName();
        });

        return iterator_to_array($roles, false);
    }
}
