<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Role;

use MsgPhp\User\Role\DefaultRoleProvider;
use MsgPhp\User\User;
use PHPUnit\Framework\TestCase;

final class DefaultRoleProviderTest extends TestCase
{
    public function testRoles(): void
    {
        $provider = new DefaultRoleProvider($roles = ['ROLE_USER', 'foo' => 'ROLE_admin', 'duplicate', 'duplicate']);

        self::assertSame($roles, $provider->getRoles($this->createMock(User::class)));
    }
}
