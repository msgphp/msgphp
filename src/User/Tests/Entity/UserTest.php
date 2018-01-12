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
        $user = new User($id = $this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');

        $this->assertSame($id, $user->getId());
        $this->assertSame('foo@bar.baz', $user->getEmail());
        $this->assertSame('secret', $user->getPassword());
        $this->assertNull($user->getPasswordResetToken());
        $this->assertNull($user->getPasswordRequestedAt());
    }

    public function testChangeEmail(): void
    {
        $user = new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');
        $user->changeEmail('other@bar.baz');

        $this->assertSame('other@bar.baz', $user->getEmail());
    }

    public function testChangePassword(): void
    {
        $user = new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');
        $user->changePassword('other');

        $this->assertSame('other', $user->getPassword());
    }

    public function testRequestPassword(): void
    {
        $user = new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');
        $user->requestPassword();

        $this->assertNotNull($user->getPasswordResetToken());

        $compareUser = new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');
        $compareUser->requestPassword();

        $this->assertNotSame($compareUser->getPasswordResetToken(), $user->getPasswordResetToken());
    }
}
