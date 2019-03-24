<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infrastructure\Security;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\User\Credential\Credential;
use MsgPhp\User\Infrastructure\Security\SecurityUser;
use MsgPhp\User\Infrastructure\Security\SecurityUserProvider;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\Role\RoleProvider;
use MsgPhp\User\ScalarUserId;
use MsgPhp\User\User;
use MsgPhp\User\UserId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityUserProviderTest extends TestCase
{
    public function testLoadUserByUsername(): void
    {
        /** @var SecurityUser $user */
        $user = (new SecurityUserProvider($this->createRepository($entity = $this->createUser())))->loadUserByUsername('username');

        self::assertInstanceOf(SecurityUser::class, $user);
        self::assertSame($entity->getId(), $user->getUserId());
        self::assertSame('id', $user->getUsername());
        self::assertSame('username', $user->getOriginUsername());
        self::assertSame([], $user->getRoles());
        self::assertSame('', $user->getPassword());
        self::assertNull($user->getSalt());
    }

    public function testLoadUserByUsernameWithOriginUsername(): void
    {
        /** @var SecurityUser $user */
        $user = (new SecurityUserProvider($this->createRepository($entity = $this->createUser())))->loadUserByUsername('origin-username');

        self::assertInstanceOf(SecurityUser::class, $user);
        self::assertSame($entity->getId(), $user->getUserId());
        self::assertSame('id', $user->getUsername());
        self::assertSame('origin-username', $user->getOriginUsername());
        self::assertSame([], $user->getRoles());
        self::assertSame('', $user->getPassword());
        self::assertNull($user->getSalt());
    }

    public function testLoadUserByUsernameWithRoles(): void
    {
        $roleProvider = $this->createMock(RoleProvider::class);
        $roleProvider->expects(self::any())
            ->method('getRoles')
            ->willReturn(['ROLE_FOO'])
        ;

        /** @var SecurityUser $user */
        $user = (new SecurityUserProvider($this->createRepository($entity = $this->createUser()), $roleProvider))->loadUserByUsername('username');

        self::assertInstanceOf(SecurityUser::class, $user);
        self::assertSame($entity->getId(), $user->getUserId());
        self::assertSame('id', $user->getUsername());
        self::assertSame('username', $user->getOriginUsername());
        self::assertSame(['ROLE_FOO'], $user->getRoles());
        self::assertSame('', $user->getPassword());
        self::assertNull($user->getPasswordAlgorithm());
        self::assertNull($user->getSalt());
    }

    public function testLoadUserByUsernameWithUnknownUsername(): void
    {
        $provider = new SecurityUserProvider($this->createRepository());

        $this->expectException(UsernameNotFoundException::class);

        $provider->loadUserByUsername('username');
    }

    public function testRefreshUser(): void
    {
        $provider = new SecurityUserProvider($this->createRepository($this->createUser()));

        /** @var SecurityUser $refreshedUser */
        $refreshedUser = $provider->refreshUser($user = $provider->loadUserByUsername('username'));

        self::assertInstanceOf(SecurityUser::class, $refreshedUser);
        self::assertEquals($user, $refreshedUser);
        self::assertNotSame($user, $refreshedUser);
        self::assertSame('username', $refreshedUser->getOriginUsername());
    }

    public function testRefreshUserWithOriginUsername(): void
    {
        $provider = new SecurityUserProvider($this->createRepository($this->createUser()));

        /** @var SecurityUser $refreshedUser */
        $refreshedUser = $provider->refreshUser($user = $provider->loadUserByUsername('origin-username'));

        self::assertInstanceOf(SecurityUser::class, $refreshedUser);
        self::assertEquals($user, $refreshedUser);
        self::assertNotSame($user, $refreshedUser);
        self::assertSame('origin-username', $refreshedUser->getOriginUsername());
    }

    public function testRefreshUserWithUnknownUser(): void
    {
        $provider = new SecurityUserProvider($this->createRepository());

        $this->expectException(UsernameNotFoundException::class);

        $provider->refreshUser(new SecurityUser($this->createUser()));
    }

    public function testRefreshUserWithUnsupportedUser(): void
    {
        $provider = new SecurityUserProvider($this->createMock(UserRepository::class));

        $this->expectException(UnsupportedUserException::class);

        $provider->refreshUser($this->createMock(UserInterface::class));
    }

    public function testSupportsClass(): void
    {
        $provider = new SecurityUserProvider($this->createMock(UserRepository::class));

        self::assertTrue($provider->supportsClass(SecurityUser::class));
        self::assertFalse($provider->supportsClass(UserInterface::class));
    }

    public function testFromUser(): void
    {
        $roleProvider = $this->createMock(RoleProvider::class);
        $roleProvider->expects(self::once())
            ->method('getRoles')
            ->willReturn(['ROLE_FOO'])
        ;

        $provider = new SecurityUserProvider($this->createMock(UserRepository::class), $roleProvider);
        $securityUser = $provider->fromUser($user = $this->createUser());

        self::assertSame($user->getId(), $securityUser->getUserId());
        self::assertSame('id', $securityUser->getUsername());
        self::assertNull($securityUser->getOriginUsername());
        self::assertSame(['ROLE_FOO'], $securityUser->getRoles());
        self::assertSame('', $securityUser->getPassword());
        self::assertNull($securityUser->getPasswordAlgorithm());
        self::assertNull($securityUser->getSalt());
    }

    public function testFromUserWithOriginUsername(): void
    {
        $provider = new SecurityUserProvider($this->createMock(UserRepository::class));
        $securityUser = $provider->fromUser($user = $this->createUser(), 'origin-username');

        self::assertSame('origin-username', $securityUser->getOriginUsername());
    }

    private function createRepository(User $user = null): UserRepository
    {
        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::any())
            ->method('find')
            ->willReturnCallback(function (UserId $id) use ($user) {
                if (null === $user || $id->toString() !== $user->getId()->toString()) {
                    throw EntityNotFoundException::createForId(User::class, $id->toString());
                }

                return $user;
            })
        ;
        $repository->expects(self::any())
            ->method('findByUsername')
            ->willReturnCallback(function (string $username) use ($user) {
                if (null !== $user && \in_array($username, ['username', 'origin-username'], true)) {
                    return $user;
                }

                throw EntityNotFoundException::createForFields(User::class, ['username' => $username]);
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
