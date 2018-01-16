<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Security;

use MsgPhp\User\{CredentialInterface, UserIdInterface};
use MsgPhp\User\Entity\User;
use MsgPhp\User\Infra\Security\SecurityUser;
use MsgPhp\User\Password\{PasswordAlgorithm, PasswordProtectedInterface, PasswordSalt};
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityUserTest extends TestCase
{
    public function testCreate(): void
    {
        $user = new SecurityUser($this->createUser('id'), ['ROLE_FOO']);

        $this->assertSame('id', $user->getUsername());
        $this->assertSame(['ROLE_FOO'], $user->getRoles());
        $this->assertSame('', $user->getPassword());
        $this->assertNull($user->getSalt());
        $this->assertEquals($user, unserialize(serialize($user)));
    }

    public function testCreateWithPassword(): void
    {
        $user = new SecurityUser($this->createUser('id', 'password'));

        $this->assertSame('password', $user->getPassword());
        $this->assertNull($user->getSalt());
        $this->assertEquals($user, unserialize(serialize($user)));
    }

    public function testCreateWithSaltedPassword(): void
    {
        $user = new SecurityUser($this->createUser('id', 'password', 'salt'));

        $this->assertSame('password', $user->getPassword());
        $this->assertSame('salt', $user->getSalt());
        $this->assertEquals($user, unserialize(serialize($user)));
    }

    public function testEraseCredentials(): void
    {
        $user = new SecurityUser($this->createUser('id', 'password', 'salt'));
        $user->eraseCredentials();

        $this->assertSame('', $user->getPassword());
        $this->assertNull($user->getSalt());
    }

    public function testIsEqualTo(): void
    {
        $user = new SecurityUser($this->createUser('id'));

        $this->assertTrue($user->isEqualTo($user));
        $this->assertTrue($user->isEqualTo(new SecurityUser($this->createUser('id'))));
        $this->assertTrue($user->isEqualTo(new SecurityUser($this->createUser('id', 'password', 'salt'))));
        $this->assertFalse($user->isEqualTo(new SecurityUser($this->createUser('other'))));

        $other = $this->createMock(UserInterface::class);
        $other->expects($this->any())
            ->method('getUsername')
            ->willReturn('id');

        $this->assertFalse($user->isEqualTo($other));
    }

    private function createUser(string $id, string $password = null, string $salt = null): User
    {
        $userId = $this->createMock(UserIdInterface::class);
        $userId->expects($this->any())
            ->method('toString')
            ->willReturn($id);
        $user = $this->createMock(User::class);
        $user->expects($this->any())
            ->method('getId')
            ->willReturn($userId);

        if (null === $password) {
            $credential = $this->createMock(CredentialInterface::class);
        } else {
            $credential = $this->createMock([CredentialInterface::class, PasswordProtectedInterface::class]);
            $credential->expects($this->any())
                ->method('getPassword')
                ->willReturn($password);
            $credential->expects($this->any())
                ->method('getPasswordAlgorithm')
                ->willReturn(null === $salt ? PasswordAlgorithm::create() : PasswordAlgorithm::createLegacySalted(new PasswordSalt($salt)));
        }

        $user->expects($this->any())
            ->method('getCredential')
            ->willReturn($credential);

        return $user;
    }
}
