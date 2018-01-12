<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity;

use MsgPhp\User\Entity\{User, UserSecondaryEmail};
use PHPUnit\Framework\TestCase;

final class UserSecondaryEmailTest extends TestCase
{
    public function testCreate(): void
    {
        $userEmail = new UserSecondaryEmail($user = $this->createMock(User::class), 'foo@bar.baz');

        $this->assertSame($user, $userEmail->getUser());
        $this->assertSame('foo@bar.baz', $userEmail->getEmail());
        $this->assertNotNull($userEmail->getToken());
        $this->assertNotSame((new UserSecondaryEmail($this->createMock(User::class), 'foo@bar.baz'))->getToken(), $userEmail->getToken());
        $this->assertFalse($userEmail->isPendingPrimary());
        $this->assertNull($userEmail->getConfirmedAt());
    }

    public function testConfirm(): void
    {
        $userEmail = new UserSecondaryEmail($this->createMock(User::class), 'foo@bar.baz');
        $userEmail->confirm();

        $this->assertNull($userEmail->getToken());
        $this->assertInstanceOf(\DateTimeInterface::class, $userEmail->getConfirmedAt());
    }

    public function testMarkPendingPrimary(): void
    {
        $userEmail = new UserSecondaryEmail($this->createMock(User::class), 'foo@bar.baz');
        $userEmail->markPendingPrimary();

        $this->assertTrue($userEmail->isPendingPrimary());

        $userEmail->markPendingPrimary(false);

        $this->assertFalse($userEmail->isPendingPrimary());

        $userEmail->confirm();

        $this->expectException(\LogicException::class);

        $userEmail->markPendingPrimary();
    }
}
