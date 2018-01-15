<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity;

use MsgPhp\User\Entity\{Credential, User};
use MsgPhp\User\UserIdInterface;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testCreate(): void
    {
        $user = new User($id = $this->createMock(UserIdInterface::class));

        $this->assertSame($id, $user->getId());
        $this->assertInstanceOf(Credential\Anonymous::class, $user->getCredential());
    }
}
