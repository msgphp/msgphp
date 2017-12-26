<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity;

use MsgPhp\User\Entity\User;
use MsgPhp\User\Entity\UserSecondaryEmail;
use MsgPhp\User\UserIdInterface;
use PHPUnit\Framework\TestCase;

final class UserSecondaryEmailTest extends TestCase
{
    public function testCreate(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');
        $userEmail = new UserSecondaryEmail($user, 'other@bar.baz');

        $this->assertSame($user, $userEmail->getUser());
        $this->assertSame($user->getId(), $userEmail->getUserId());
        $this->assertSame('other@bar.baz', $userEmail->getEmail());
        $this->assertInternalType('string', $userEmail->getToken());
        $this->assertFalse($userEmail->isPendingPrimary());
        $this->assertNull($userEmail->getConfirmedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $userEmail->getCreatedAt());
    }

    public function testCreateDuplicateEmail(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');

        $this->expectException(\LogicException::class);

        new UserSecondaryEmail($user, 'foo@bar.baz');
    }

    public function testConfirm(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');
        $userEmail = new UserSecondaryEmail($user, 'other@bar.baz');
        $userEmail->confirm();

        $compareUserEmail = new UserSecondaryEmail($user, 'other@bar.baz');
        $this->assertNotSame($userEmail->getToken(), $compareUserEmail->getToken());

        $this->assertNull($userEmail->getToken());
        $this->assertInstanceOf(\DateTimeInterface::class, $userEmail->getConfirmedAt());
    }

    public function testMarkPendingPrimary(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');
        $userEmail = new UserSecondaryEmail($user, 'other@bar.baz');
        $userEmail->markPendingPrimary();

        $this->assertTrue($userEmail->isPendingPrimary());

        $userEmail->markPendingPrimary(false);

        $this->assertFalse($userEmail->isPendingPrimary());

        $userEmail->confirm();

        $this->expectException(\LogicException::class);

        $userEmail->markPendingPrimary();
    }
}
