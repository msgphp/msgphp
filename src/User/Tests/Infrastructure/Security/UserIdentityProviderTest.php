<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infrastructure\Security;

use MsgPhp\Domain\Exception\EntityNotFound;
use MsgPhp\User\Credential\Credential;
use MsgPhp\User\Infrastructure\Security\UserIdentity;
use MsgPhp\User\Infrastructure\Security\UserIdentityProvider;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\Role\RoleProvider;
use MsgPhp\User\ScalarUserId;
use MsgPhp\User\User;
use MsgPhp\User\UserId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserIdentityProviderTest extends TestCase
{
    public function testLoadUserByUsername(): void
    {
        $identity = (new UserIdentityProvider($this->createRepository($entity = $this->createUser())))->loadUserByUsername('username');

        self::assertSame($entity->getId(), $identity->getUserId());
        self::assertSame('id', $identity->getUsername());
        self::assertSame('username', $identity->getOriginUsername());
        self::assertSame([], $identity->getRoles());
        self::assertNull($identity->getPassword());
        self::assertNull($identity->getSalt());
        self::assertSame(UserIdentity::class, $identity->getEncoderName());
    }

    public function testLoadUserByUsernameWithOriginUsername(): void
    {
        $identity = (new UserIdentityProvider($this->createRepository($entity = $this->createUser())))->loadUserByUsername('origin-username');

        self::assertSame($entity->getId(), $identity->getUserId());
        self::assertSame('id', $identity->getUsername());
        self::assertSame('origin-username', $identity->getOriginUsername());
        self::assertSame([], $identity->getRoles());
        self::assertNull($identity->getPassword());
        self::assertNull($identity->getSalt());
        self::assertSame(UserIdentity::class, $identity->getEncoderName());
    }

    public function testLoadUserByUsernameWithRoles(): void
    {
        $roleProvider = $this->createMock(RoleProvider::class);
        $roleProvider->expects(self::any())
            ->method('getRoles')
            ->willReturn(['ROLE_FOO'])
        ;
        $identity = (new UserIdentityProvider($this->createRepository($entity = $this->createUser()), $roleProvider))->loadUserByUsername('username');

        self::assertSame($entity->getId(), $identity->getUserId());
        self::assertSame('id', $identity->getUsername());
        self::assertSame('username', $identity->getOriginUsername());
        self::assertSame(['ROLE_FOO'], $identity->getRoles());
        self::assertNull($identity->getPassword());
        self::assertNull($identity->getSalt());
        self::assertSame(UserIdentity::class, $identity->getEncoderName());
    }

    public function testLoadUserByUsernameWithUnknownUsername(): void
    {
        $provider = new UserIdentityProvider($this->createRepository());

        $this->expectException(UsernameNotFoundException::class);

        $provider->loadUserByUsername('username');
    }

    public function testRefreshUser(): void
    {
        $provider = new UserIdentityProvider($this->createRepository($this->createUser()));
        $refreshedIdentity = $provider->refreshUser($user = $provider->loadUserByUsername('username'));

        self::assertEquals($user, $refreshedIdentity);
        self::assertNotSame($user, $refreshedIdentity);
        self::assertSame('username', $refreshedIdentity->getOriginUsername());
    }

    public function testRefreshUserWithOriginUsername(): void
    {
        $provider = new UserIdentityProvider($this->createRepository($this->createUser()));
        $refreshedIdentity = $provider->refreshUser($user = $provider->loadUserByUsername('origin-username'));

        self::assertEquals($user, $refreshedIdentity);
        self::assertNotSame($user, $refreshedIdentity);
        self::assertSame('origin-username', $refreshedIdentity->getOriginUsername());
    }

    public function testRefreshUserWithUnknownUser(): void
    {
        $provider = new UserIdentityProvider($this->createRepository());

        $this->expectException(UsernameNotFoundException::class);

        $provider->refreshUser(new UserIdentity($this->createUser()));
    }

    public function testRefreshUserWithUnsupportedUser(): void
    {
        $provider = new UserIdentityProvider($this->createMock(UserRepository::class));

        $this->expectException(UnsupportedUserException::class);

        $provider->refreshUser($this->createMock(UserInterface::class));
    }

    public function testSupportsClass(): void
    {
        $provider = new UserIdentityProvider($this->createMock(UserRepository::class));

        self::assertTrue($provider->supportsClass(UserIdentity::class));
        self::assertFalse($provider->supportsClass(UserInterface::class));
    }

    public function testFromUser(): void
    {
        $roleProvider = $this->createMock(RoleProvider::class);
        $roleProvider->expects(self::once())
            ->method('getRoles')
            ->willReturn(['ROLE_FOO'])
        ;

        $provider = new UserIdentityProvider($this->createMock(UserRepository::class), $roleProvider);
        $identity = $provider->fromUser($user = $this->createUser());

        self::assertSame($user->getId(), $identity->getUserId());
        self::assertSame('id', $identity->getUsername());
        self::assertNull($identity->getOriginUsername());
        self::assertSame(['ROLE_FOO'], $identity->getRoles());
        self::assertNull($identity->getPassword());
        self::assertNull($identity->getSalt());
        self::assertSame(UserIdentity::class, $identity->getEncoderName());
    }

    public function testFromUserWithOriginUsername(): void
    {
        $provider = new UserIdentityProvider($this->createMock(UserRepository::class));
        $identity = $provider->fromUser($user = $this->createUser(), 'origin-username');

        self::assertSame('origin-username', $identity->getOriginUsername());
    }

    private function createRepository(User $user = null): UserRepository
    {
        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::any())
            ->method('find')
            ->willReturnCallback(static function (UserId $id) use ($user) {
                if (null === $user || $id->toString() !== $user->getId()->toString()) {
                    throw EntityNotFound::createForId(User::class, $id->toString());
                }

                return $user;
            })
        ;
        $repository->expects(self::any())
            ->method('findByUsername')
            ->willReturnCallback(static function (string $username) use ($user) {
                if (null !== $user && \in_array($username, ['username', 'origin-username'], true)) {
                    return $user;
                }

                throw EntityNotFound::createForFields(User::class, ['username' => $username]);
            })
        ;

        return $repository;
    }

    private function createUser(): User
    {
        $user = $this->createMock(User::class);
        $user->expects(self::any())
            ->method('getId')
            ->willReturn(new ScalarUserId('id'))
        ;
        $user->expects(self::any())
            ->method('getCredential')
            ->willReturn($this->createMock(Credential::class))
        ;

        return $user;
    }
}
