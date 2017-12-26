<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity;

use MsgPhp\User\Entity\User;
use MsgPhp\User\Entity\UserRole;
use MsgPhp\User\UserIdInterface;
use PHPUnit\Framework\TestCase;

final class UserRoleTest extends TestCase
{
    public function testCreate(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');
        $userRole = new UserRole($user, 'ROLE_USER');

        $this->assertSame($user, $userRole->getUser());
        $this->assertSame($user->getId(), $userRole->getUserId());
        $this->assertSame('ROLE_USER', $userRole->getRole());
        $this->assertInstanceOf(\DateTimeInterface::class, $userRole->getCreatedAt());
    }
}
