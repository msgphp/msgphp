<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Role;

use MsgPhp\Domain\GenericDomainCollection;
use MsgPhp\User\Repository\UserRoleRepositoryInterface;
use MsgPhp\User\Role\UserRoleProvider;
use MsgPhp\User\User;
use MsgPhp\User\UserIdInterface;
use MsgPhp\User\UserRole;
use PHPUnit\Framework\TestCase;

final class UserRoleProviderTest extends TestCase
{
    public function testRoles(): void
    {
        $user = $this->createMock(User::class);
        $user->expects(self::once())
            ->method('getId')
            ->willReturn($id = $this->createMock(UserIdInterface::class))
        ;
        $userRole = $this->createMock(UserRole::class);
        $userRole
            ->method('getRoleName')
            ->willReturn('ROLE_USER')
        ;
        $repository = $this->createMock(UserRoleRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findAllByUserId')
            ->with($id)
            ->willReturn(new GenericDomainCollection(['foo' => $userRole]))
        ;

        self::assertSame(['ROLE_USER'], (new UserRoleProvider($repository))->getRoles($user));
    }
}
