<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Security;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Infra\Security\{SecurityUser, SecurityUserProvider, UserRolesProviderInterface};
use MsgPhp\User\Repository\UserRepositoryInterface;
use MsgPhp\User\{CredentialInterface, UserIdInterface};
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
        self::assertSame(['ROLE_USER'], $user->getRoles());
        self::assertSame('', $user->getPassword());
        self::assertNull($user->getSalt());
    }

    public function testLoadUserByUsernameWithOriginUsername(): void
    {
        /** @var SecurityUser $user */
        $user = (new SecurityUserProvider($this->createRepository($entity = $this->createUser(), 'origin-username')))->loadUserByUsername('origin-username');

        self::assertInstanceOf(SecurityUser::class, $user);
        self::assertSame($entity->getId(), $user->getUserId());
        self::assertSame('id', $user->getUsername());
        self::assertSame('origin-username', $user->getOriginUsername());
        self::assertSame(['ROLE_USER'], $user->getRoles());
        self::assertSame('', $user->getPassword());
        self::assertNull($user->getSalt());
    }

    public function testLoadUserByUsernameWithRoles(): void
    {
        $rolesProvider = $this->createMock(UserRolesProviderInterface::class);
        $rolesProvider->expects(self::any())
            ->method('getRoles')
            ->willReturn($roles = ['ROLE_FOO']);

        /** @var SecurityUser $user */
        $user = (new SecurityUserProvider($this->createRepository($entity = $this->createUser()), $rolesProvider))->loadUserByUsername('username');

        self::assertInstanceOf(SecurityUser::class, $user);
        self::assertSame($entity->getId(), $user->getUserId());
        self::assertSame('id', $user->getUsername());
        self::assertSame('username', $user->getOriginUsername());
        self::assertSame($roles, $user->getRoles());
        self::assertSame('', $user->getPassword());
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

        /** @var SecurityUser $user */
        $user = $provider->refreshUser($originUser = $provider->loadUserByUsername('username'));

        self::assertInstanceOf(SecurityUser::class, $user);
        self::assertEquals($originUser, $user);
        self::assertNotSame($originUser, $user);
        self::assertSame('username', $user->getOriginUsername());
    }

    public function testRefreshUserWithOriginUsername(): void
    {
        $provider = new SecurityUserProvider($this->createRepository($this->createUser(), 'origin-username'));

        /** @var SecurityUser $user */
        $user = $provider->refreshUser($originUser = $provider->loadUserByUsername('origin-username'));

        self::assertInstanceOf(SecurityUser::class, $user);
        self::assertEquals($originUser, $user);
        self::assertNotSame($originUser, $user);
        self::assertSame('origin-username', $user->getOriginUsername());
    }

    public function testRefreshUserWithUnknownUser(): void
    {
        $provider = new SecurityUserProvider($this->createRepository());

        $this->expectException(UsernameNotFoundException::class);

        $provider->refreshUser(new SecurityUser($this->createUser()));
    }

    public function testRefreshUserWithUnsupportedUser(): void
    {
        $provider = new SecurityUserProvider($this->createMock(UserRepositoryInterface::class));

        $this->expectException(UnsupportedUserException::class);

        $provider->refreshUser($this->createMock(UserInterface::class));
    }

    public function testSupportsClass(): void
    {
        $provider = new SecurityUserProvider($this->createMock(UserRepositoryInterface::class));

        self::assertTrue($provider->supportsClass(SecurityUser::class));
        self::assertFalse($provider->supportsClass(UserInterface::class));
    }

    public function testFromUser(): void
    {
        $rolesProvider = $this->createMock(UserRolesProviderInterface::class);
        $rolesProvider->expects(self::once())
            ->method('getRoles')
            ->willReturn($roles = ['ROLE_FOO']);

        $provider = new SecurityUserProvider($this->createMock(UserRepositoryInterface::class), $rolesProvider);
        $securityUser = $provider->fromUser($user = $this->createUser());

        self::assertSame($user->getId(), $securityUser->getUserId());
        self::assertSame('id', $securityUser->getUsername());
        self::assertSame('username', $securityUser->getOriginUsername());
        self::assertSame('', $securityUser->getPassword());
        self::assertNull($securityUser->getSalt());
        self::assertSame($roles, $securityUser->getRoles());
    }

    public function testFromUserWithOriginUsername(): void
    {
        $provider = new SecurityUserProvider($this->createMock(UserRepositoryInterface::class));
        $securityUser = $provider->fromUser($user = $this->createUser(), 'origin-username');

        self::assertSame('origin-username', $securityUser->getOriginUsername());
    }

    private function createRepository(User $user = null, string $originUsername = null): UserRepositoryInterface
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects(self::any())
            ->method('find')
            ->willReturnCallback(function (UserIdInterface $id) use ($user) {
                if (null === $user || $id->toString() !== $user->getId()->toString()) {
                    throw EntityNotFoundException::createForId(User::class, $id->toString());
                }

                return $user;
            });
        $repository->expects(self::any())
            ->method('findByUsername')
            ->willReturnCallback(function (string $username) use ($user, $originUsername) {
                if (null === $user || ($username !== $originUsername && $username !== $user->getCredential()->getUsername())) {
                    throw EntityNotFoundException::createForFields(User::class, ['username' => $username]);
                }

                return $user;
            });

        return $repository;
    }

    private function createUser(string $id = 'id', string $username = 'username'): User
    {
        $user = $this->createMock(User::class);

        $user->expects(self::any())
            ->method('getId')
            ->willReturn($userId = $this->createMock(UserIdInterface::class));
        $userId->expects(self::any())
            ->method('toString')
            ->willReturn($id);

        $user->expects(self::any())
            ->method('getCredential')
            ->willReturn($credential = $this->createMock(CredentialInterface::class));
        $credential->expects(self::any())
            ->method('getUsername')
            ->willReturn($username);

        return $user;
    }
}
