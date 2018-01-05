<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Security;

use MsgPhp\Domain\DomainId;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Infra\Security\SecurityUser;
use MsgPhp\User\UserIdInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityUserTest extends TestCase
{
    public function testCreateNewSecurityUser(): void
    {
        $user = new User($id = $this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');
        $securityUser = new SecurityUser($user);

        $this->assertSame($id, $securityUser->getId());
        $this->assertSame('foo@bar.baz', $securityUser->getUsername());
        $this->assertSame('secret', $securityUser->getPassword());
        $this->assertNull($securityUser->getSalt());
    }

    public function testEraseCredentials(): void
    {
        $user = new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');
        $securityUser = new SecurityUser($user);
        $securityUser->eraseCredentials();

        $this->assertSame('secret', $securityUser->getPassword());
    }

    public function testIsEqualTo(): void
    {
        $id = $this->createMock(UserIdInterface::class);
        $id->expects($this->any())
            ->method('equals')
            ->willReturnCallback(function (UserIdInterface $other) {
                return '1' === $other->toString();
            });
        $id->expects($this->any())
            ->method('toString')
            ->willReturn('1');
        $user = new User($id, 'foo@bar.baz', 'secret');
        $securityUser = new SecurityUser($user);

        $this->assertTrue($securityUser->isEqualTo(new SecurityUser($user)));
        $this->assertTrue($securityUser->isEqualTo(new SecurityUser(new User($id, 'foo@bar.baz', 'secret'))));
        $this->assertFalse($securityUser->isEqualTo(new SecurityUser(new User($id, 'foo@bar.baz', 'other'))));
        $this->assertFalse($securityUser->isEqualTo(new SecurityUser(new User($id, 'other', 'secret'))));
        $this->assertFalse($securityUser->isEqualTo(new SecurityUser(new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret'))));
        $this->assertFalse($securityUser->isEqualTo($this->createMock(UserInterface::class)));
    }

    public function testSerialize(): void
    {
        $user = new User(new SerializableUserId(), 'foo@bar.baz', 'secret');

        $this->assertTrue(($serialized = serialize(new SecurityUser($user))) === serialize(unserialize($serialized)));
    }
}

class SerializableUserId extends DomainId implements UserIdInterface
{
}
