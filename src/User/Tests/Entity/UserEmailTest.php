<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity;

use MsgPhp\User\Entity\{User, UserEmail};
use PHPUnit\Framework\TestCase;

final class UserEmailTest extends TestCase
{
    public function testCreate(): void
    {
        $userEmail = $this->createEntity($user = $this->createMock(User::class), 'foo@bar.baz', null);

        $this->assertSame($user, $userEmail->getUser());
        $this->assertSame('foo@bar.baz', $userEmail->getEmail());
        $this->assertNotNull($userEmail->getConfirmationToken());
        $this->assertNotSame($userEmail->getConfirmationToken(), $this->createEntity($user, 'foo@bar.baz', null)->getConfirmationToken());
        $this->assertNull($userEmail->getConfirmedAt());
    }

    public function testCreateWithToken(): void
    {
        $this->assertSame('token', $this->createEntity($this->createMock(User::class), 'foo@bar.baz', 'token')->getConfirmationToken());
    }

    private function createEntity($user, $email, $token): UserEmail
    {
        return new class($user, $email, $token) extends UserEmail {
        };
    }
}
