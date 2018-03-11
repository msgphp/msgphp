<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity;

use MsgPhp\User\Entity\{Role, User, UserRole};
use PHPUnit\Framework\TestCase;

final class UserRoleTest extends TestCase
{
    public function testCreate(): void
    {
        $userRole = $this->createEntity($user = $this->createMock(User::class), $role = $this->createMock(Role::class));

        $this->assertSame($user, $userRole->getUser());
        $this->assertSame($role, $userRole->getRole());
    }

    private function createEntity($user, $role): UserRole
    {
        return new class($user, $role) extends UserRole {
        };
    }
}
