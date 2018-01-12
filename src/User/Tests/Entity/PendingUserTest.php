<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity;

use MsgPhp\User\Entity\PendingUser;
use PHPUnit\Framework\TestCase;

final class PendingUserTest extends TestCase
{
    public function testCreate(): void
    {
        $user = new PendingUser('foo@bar.baz', 'secret');

        $this->assertSame('foo@bar.baz', $user->getEmail());
        $this->assertSame('secret', $user->getPassword());
        $this->assertNotNull($user->getToken());
        $this->assertNotSame((new PendingUser('foo@bar.baz', 'secret'))->getToken(), $user->getToken());
    }
}
