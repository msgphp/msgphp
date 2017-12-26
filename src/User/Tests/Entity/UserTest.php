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
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');

        $this->assertInstanceOf(UserIdInterface::class, $user->getId());
        $this->assertSame('foo@bar.baz', $user->getEmail());
        $this->assertSame('secret', $user->getPassword());
        $this->assertFalse($user->isEnabled());
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getCreatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getLastUpdatedAt());
        $this->assertNull($user->getPasswordResetToken());
        $this->assertNull($user->getPasswordRequestedAt());
    }

    public function testChangeEmail(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');
        $lastUpdatedAt = $user->getLastUpdatedAt();

        $user->changeEmail('other@bar.baz');

        $this->assertSame('other@bar.baz', $user->getEmail());
        $this->assertGreaterThanOrEqual($lastUpdatedAt, $user->getLastUpdatedAt());
    }

    public function testChangePassword(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');
        $lastUpdatedAt = $user->getLastUpdatedAt();

        $user->changePassword('other');

        $this->assertSame('other', $user->getPassword());
        $this->assertGreaterThanOrEqual($lastUpdatedAt, $user->getLastUpdatedAt());
    }

    public function testRequestPassword(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');
        $lastUpdatedAt = $user->getLastUpdatedAt();

        $user->requestPassword();

        $this->assertInternalType('string', $user->getPasswordResetToken());
        $this->assertGreaterThanOrEqual($lastUpdatedAt, $user->getLastUpdatedAt());
        $this->assertGreaterThanOrEqual($lastUpdatedAt, $user->getPasswordRequestedAt());
        $this->assertGreaterThanOrEqual($user->getPasswordRequestedAt(), $user->getLastUpdatedAt());

        $compareUser = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');
        $compareUser->requestPassword();

        $this->assertNotSame($user->getPasswordResetToken(), $compareUser->getPasswordResetToken());
    }

    public function testEnableDisable(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');
        $lastUpdatedAt = $user->getLastUpdatedAt();

        $user->enable();

        $this->assertTrue($user->isEnabled());
        $this->assertGreaterThanOrEqual($lastUpdatedAt, $lastUpdatedAt = $user->getLastUpdatedAt());

        $user->disable();

        $this->assertFalse($user->isEnabled());
        $this->assertGreaterThanOrEqual($lastUpdatedAt, $user->getLastUpdatedAt());
    }
}
