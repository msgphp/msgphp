<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Role;

use MsgPhp\User\Entity\User;
use MsgPhp\User\Role\DefaultRoleProvider;
use PHPUnit\Framework\TestCase;

final class DefaultRoleProviderTest extends TestCase
{
    public function testRoles(): void
    {
        $provider = new DefaultRoleProvider(['ROLE_USER', 'foo' => 'ROLE_admin', 'duplicate', 'duplicate']);

        self::assertSame(['ROLE_USER', 'ROLE_admin', 'duplicate'], $provider->getRoles($this->createMock(User::class)));
    }

    public function testRolesWithoutSanitizing(): void
    {
        $provider = new DefaultRoleProvider($expected = ['ROLE_USER', 'foo' => 'ROLE_admin', 'duplicate', 'duplicate'], false);

        self::assertSame($expected, $provider->getRoles($this->createMock(User::class)));
    }
}
