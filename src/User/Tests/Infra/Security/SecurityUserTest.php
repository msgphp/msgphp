<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Security;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\DomainIdInterface;
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
        $user = new SecurityUser($entity = $this->createUser('id'), null, ['ROLE_FOO']);

        self::assertSame($entity->getId(), $user->getUserId());
        self::assertSame('username', $user->getOriginUsername());
        self::assertSame('id', $user->getUsername());
        self::assertSame(['ROLE_FOO'], $user->getRoles());
        self::assertSame('', $user->getPassword());
        self::assertNull($user->getSalt());
    }

    public function testCreateWithOriginUsername(): void
    {
        self::assertSame('origin-username', (new SecurityUser($this->createUser('id'), 'origin-username'))->getOriginUsername());
    }

    public function testCreateWithPassword(): void
    {
        $user = new SecurityUser($this->createUser('id', 'password'));

        self::assertSame('password', $user->getPassword());
        self::assertNull($user->getSalt());
    }

    public function testCreateWithSaltedPassword(): void
    {
        $user = new SecurityUser($this->createUser('id', 'password', 'salt'));

        self::assertSame('password', $user->getPassword());
        self::assertSame('salt', $user->getSalt());
    }

    public function testCreateWithEmptyId(): void
    {
        $user = $this->createUser();

        $this->expectException(\LogicException::class);

        new SecurityUser($user);
    }

    public function testEraseCredentials(): void
    {
        $user = new SecurityUser($this->createUser('id', 'password', 'salt'));
        $user->eraseCredentials();

        self::assertSame('', $user->getPassword());
        self::assertNull($user->getSalt());
    }

    public function testIsEqualTo(): void
    {
        $user = new SecurityUser($this->createUser('id'));

        self::assertTrue($user->isEqualTo($user));
        self::assertTrue($user->isEqualTo(new SecurityUser($this->createUser('id'))));
        self::assertTrue($user->isEqualTo(new SecurityUser($this->createUser('id', 'password', 'salt'))));
        self::assertFalse($user->isEqualTo(new SecurityUser($this->createUser('other'))));
    }

    public function testIsEqualToWithOtherUserType(): void
    {
        $other = $this->createMock(UserInterface::class);
        $other->expects(self::any())
            ->method('getUsername')
            ->willReturn('id')
        ;

        self::assertFalse((new SecurityUser($this->createUser('id')))->isEqualTo($other));
    }

    public function testIsEqualToWithOtherOriginUsername(): void
    {
        self::assertTrue((new SecurityUser($this->createUser('id')))->isEqualTo(new SecurityUser($this->createUser('id'), 'origin-username')));
    }

    public function testSerialize(): void
    {
        $user = new SecurityUser($this->createUser(new TestUserId('id'), 'password', 'salt'), 'origin-username', ['ROLE_FOO']);

        self::assertEquals($user, unserialize(serialize($user)));
    }

    private function createUser($id = null, string $password = null, string $salt = null): User
    {
        if ($id instanceof UserIdInterface) {
            $userId = $id;
        } else {
            $userId = $this->createMock(UserIdInterface::class);
            $userId->expects(self::any())
                ->method('toString')
                ->willReturn($id)
            ;
            $userId->expects(self::any())
                ->method('equals')
                ->willReturnCallback(function (DomainIdInterface $other) use ($id) {
                    return $id === $other->toString();
                })
            ;
            $userId->expects(self::any())
                ->method('isEmpty')
                ->willReturn(null === $id)
            ;
        }

        $user = $this->createMock(User::class);
        $user->expects(self::any())
            ->method('getId')
            ->willReturn($userId)
        ;

        if (null === $password) {
            $credential = $this->createMock(CredentialInterface::class);
        } else {
            $credential = $this->createMock([CredentialInterface::class, PasswordProtectedInterface::class]);
            $credential->expects(self::any())
                ->method('getPassword')
                ->willReturn($password)
            ;
            $credential->expects(self::any())
                ->method('getPasswordAlgorithm')
                ->willReturn(null === $salt ? PasswordAlgorithm::create() : PasswordAlgorithm::createLegacySalted(new PasswordSalt($salt)))
            ;
        }

        $credential->expects(self::any())
            ->method('getUsername')
            ->willReturn('username')
        ;

        $user->expects(self::any())
            ->method('getCredential')
            ->willReturn($credential)
        ;

        return $user;
    }
}

class TestUserId extends DomainId implements UserIdInterface
{
}
