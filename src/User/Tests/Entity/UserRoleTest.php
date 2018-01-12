<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity;

use MsgPhp\User\Entity\{User, UserRole};
use PHPUnit\Framework\TestCase;

final class UserRoleTest extends TestCase
{
    public function testCreate(): void
    {
        $userRole = new UserRole($user = $this->createMock(User::class), 'ROLE_USER');

        $this->assertSame($user, $userRole->getUser());
        $this->assertSame('ROLE_USER', $userRole->getRole());
    }
}
