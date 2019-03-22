<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests;

use MsgPhp\User\Role;
use MsgPhp\User\User;
use MsgPhp\User\UserRole;
use PHPUnit\Framework\TestCase;

final class UserRoleTest extends TestCase
{
    public function testCreate(): void
    {
        $userRole = $this->createEntity($user = $this->createMock(User::class), $role = $this->createMock(Role::class));

        self::assertSame($user, $userRole->getUser());
        self::assertSame($role, $userRole->getRole());
    }

    private function createEntity($user, $role): UserRole
    {
        return new class($user, $role) extends UserRole {
        };
    }
}
