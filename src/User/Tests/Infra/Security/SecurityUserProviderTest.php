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

        $this->assertInstanceOf(SecurityUser::class, $user);
        $this->assertSame($entity->getId(), $user->getUserId());
        $this->assertSame('id', $user->getUsername());
        $this->assertSame(['ROLE_USER'], $user->getRoles());
        $this->assertSame('', $user->getPassword());
        $this->assertNull($user->getSalt());
    }

    public function testLoadUserByUsernameWithRoles(): void
    {
        $rolesProvider = $this->createMock(UserRolesProviderInterface::class);
        $rolesProvider->expects($this->any())
            ->method('getRoles')
            ->willReturn($roles = ['ROLE_FOO']);

        /** @var SecurityUser $user */
        $user = (new SecurityUserProvider($this->createRepository($entity = $this->createUser()), $rolesProvider))->loadUserByUsername('username');

        $this->assertInstanceOf(SecurityUser::class, $user);
        $this->assertSame($entity->getId(), $user->getUserId());
        $this->assertSame('id', $user->getUsername());
        $this->assertSame($roles, $user->getRoles());
        $this->assertSame('', $user->getPassword());
        $this->assertNull($user->getSalt());
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
        $user = $provider->refreshUser($originUser = $provider->loadUserByUsername('username'));

        $this->assertEquals($originUser, $user);
        $this->assertNotSame($originUser, $user);
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

        $this->assertTrue($provider->supportsClass(SecurityUser::class));
        $this->assertFalse($provider->supportsClass(UserInterface::class));
    }

    public function testFromUser(): void
    {
        $rolesProvider = $this->createMock(UserRolesProviderInterface::class);
        $rolesProvider->expects($this->once())
            ->method('getRoles')
            ->willReturn($roles = ['ROLE_FOO']);
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getId')
            ->willReturn($userId = $this->createMock(UserIdInterface::class));
        $userId->expects($this->once())
            ->method('toString')
            ->willReturn('123');
        $provider = new SecurityUserProvider($this->createMock(UserRepositoryInterface::class), $rolesProvider);
        $securityUser = $provider->fromUser($user);

        $this->assertSame($userId, $securityUser->getUserId());
        $this->assertSame('123', $securityUser->getUsername());
        $this->assertSame('', $securityUser->getPassword());
        $this->assertNull($securityUser->getSalt());
        $this->assertSame($roles, $securityUser->getRoles());
    }

    private function createRepository(User $user = null): UserRepositoryInterface
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->any())
            ->method('find')
            ->willReturnCallback(function (UserIdInterface $id) use ($user) {
                if (null === $user || $id->toString() !== $user->getId()->toString()) {
                    throw EntityNotFoundException::createForId(User::class, $id->toString());
                }

                return $user;
            });
        $repository->expects($this->any())
            ->method('findByUsername')
            ->willReturnCallback(function (string $username) use ($user) {
                if (null === $user || $username !== $user->getCredential()->getUsername()) {
                    throw EntityNotFoundException::createForFields(User::class, ['username' => $$username]);
                }

                return $user;
            });

        return $repository;
    }

    private function createUser(string $id = 'id', string $username = 'username'): User
    {
        $user = $this->createMock(User::class);

        $user->expects($this->any())
            ->method('getId')
            ->willReturn($userId = $this->createMock(UserIdInterface::class));
        $userId->expects($this->any())
            ->method('toString')
            ->willReturn($id);

        $user->expects($this->any())
            ->method('getCredential')
            ->willReturn($credential = $this->createMock(CredentialInterface::class));
        $credential->expects($this->any())
            ->method('getUsername')
            ->willReturn($username);

        return $user;
    }
}
