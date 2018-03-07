<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity;

use MsgPhp\User\Entity\{User, UserSecondaryEmail};
use PHPUnit\Framework\TestCase;

final class UserSecondaryEmailTest extends TestCase
{
    public function testCreate(): void
    {
        $userSecondaryEmail = $this->createEntity($user = $this->createMock(User::class), 'foo@bar.baz', null);

        $this->assertSame($user, $userSecondaryEmail->getUser());
        $this->assertSame('foo@bar.baz', $userSecondaryEmail->getEmail());
        $this->assertNotNull($userSecondaryEmail->getConfirmationToken());
        $this->assertNotSame($userSecondaryEmail->getConfirmationToken(), $this->createEntity($user, 'foo@bar.baz', null)->getConfirmationToken());
        $this->assertFalse($userSecondaryEmail->isPendingPrimary());
        $this->assertNull($userSecondaryEmail->getConfirmedAt());
    }

    public function testCreateWithToken(): void
    {
        $this->assertSame('token', $this->createEntity($this->createMock(User::class), 'foo@bar.baz', 'token')->getConfirmationToken());
    }

    public function testMarkPendingPrimary(): void
    {
        $userSecondaryEmail = $this->createEntity($this->createMock(User::class), 'foo@bar.baz', null);
        $userSecondaryEmail->markPendingPrimary();

        $this->assertTrue($userSecondaryEmail->isPendingPrimary());

        $userSecondaryEmail->markPendingPrimary(false);

        $this->assertFalse($userSecondaryEmail->isPendingPrimary());

        $userSecondaryEmail->confirm();

        $this->expectException(\LogicException::class);

        $userSecondaryEmail->markPendingPrimary();
    }

    private function createEntity($user, $email, $token): UserSecondaryEmail
    {
        return new class($user, $email, $token) extends UserSecondaryEmail {
        };
    }
}
