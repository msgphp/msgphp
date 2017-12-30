<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Security;

use MsgPhp\Domain\Infra\InMemory\DomainId;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Infra\Security\SecurityUser;
use MsgPhp\User\UserIdInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityUserTest extends TestCase
{
    public function testCreateNewSecurityUser(): void
    {
        $user = new User(new UserId(), 'foo@bar.baz', 'secret');
        $securityUser = new SecurityUser($user);

        $this->assertTrue($user->getId()->equals($securityUser->getId()));
        $this->assertSame($user->getEmail(), $securityUser->getUsername());
        $this->assertSame($user->getPassword(), $securityUser->getPassword());
    }

    public function testIsEqualTo(): void
    {
        $user = new User(new UserId(), 'foo@bar.baz', 'secret');
        $securityUser = new SecurityUser($user);

        $this->assertTrue($securityUser->isEqualTo(new SecurityUser($user)));
        $this->assertTrue($securityUser->isEqualTo(new SecurityUser(new User($user->getId(), 'foo@bar.baz', 'secret'))));
        $this->assertFalse($securityUser->isEqualTo(new SecurityUser(new User($user->getId(), 'foo@bar.baz', 'other'))));
        $this->assertFalse($securityUser->isEqualTo(new SecurityUser(new User($user->getId(), 'other', 'secret'))));
        $this->assertFalse($securityUser->isEqualTo(new SecurityUser(new User(new UserId(), 'foo@bar.baz', 'secret'))));
        $this->assertFalse($securityUser->isEqualTo($this->createMock(UserInterface::class)));
    }

    public function testSerialize(): void
    {
        $user = new User(new UserId(), 'foo@bar.baz', 'secret');

        $this->assertTrue(($serialized = serialize(new SecurityUser($user))) === serialize(unserialize($serialized)));
    }
}

class UserId extends DomainId implements UserIdInterface
{
}
