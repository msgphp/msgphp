<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity;

use MsgPhp\User\Entity\User;
use MsgPhp\User\UserIdInterface;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testCreate(): void
    {
        $now = new \DateTime();
        $user = new User($id = $this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');

        $this->assertSame($id, $user->getId());
        $this->assertSame('foo@bar.baz', $user->getEmail());
        $this->assertSame('secret', $user->getPassword());
        $this->assertNull($user->getPasswordResetToken());
        $this->assertNull($user->getPasswordRequestedAt());
        $this->assertGreaterThanOrEqual($now, $user->getCreatedAt());
        $this->assertGreaterThanOrEqual($now, $user->getLastUpdatedAt());
    }

    public function testChangeEmail(): void
    {
        $user = new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');
        $lastUpdatedAt = $user->getLastUpdatedAt();

        $user->changeEmail('other@bar.baz');

        $this->assertSame('other@bar.baz', $user->getEmail());
        $this->assertGreaterThanOrEqual($lastUpdatedAt, $user->getLastUpdatedAt());
    }

    public function testChangePassword(): void
    {
        $user = new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');
        $lastUpdatedAt = $user->getLastUpdatedAt();

        $user->changePassword('other');

        $this->assertSame('other', $user->getPassword());
        $this->assertGreaterThanOrEqual($lastUpdatedAt, $user->getLastUpdatedAt());
    }

    public function testRequestPassword(): void
    {
        $user = new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');
        $lastUpdatedAt = $user->getLastUpdatedAt();

        $user->requestPassword();

        $this->assertNotNull($user->getPasswordResetToken());
        $this->assertGreaterThanOrEqual($lastUpdatedAt, $user->getLastUpdatedAt());
        $this->assertGreaterThanOrEqual($user->getPasswordRequestedAt(), $user->getLastUpdatedAt());

        $compareUser = new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');
        $compareUser->requestPassword();

        $this->assertNotSame($compareUser->getPasswordResetToken(), $user->getPasswordResetToken());
    }
}
