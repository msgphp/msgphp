<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity;

use MsgPhp\User\Entity\{User, UserEmail};
use PHPUnit\Framework\TestCase;

final class UserEmailTest extends TestCase
{
    public function testCreate(): void
    {
        $userEmail = $this->createEntity($user = $this->createMock(User::class), 'foo@bar.baz');

        $this->assertSame($user, $userEmail->getUser());
        $this->assertSame('foo@bar.baz', $userEmail->getEmail());
    }

    private function createEntity($user, $email): UserEmail
    {
        return new class($user, $email) extends UserEmail {
        };
    }
}
