<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Security;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
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
        $user = (new SecurityUserProvider($this->createRepository($this->createUser()), $this->createFactory()))->loadUserByUsername('username');

        $this->assertSame('id', $user->getUsername());
        $this->assertSame([], $user->getRoles());
        $this->assertSame('', $user->getPassword());
        $this->assertNull($user->getSalt());
    }

    public function testLoadUserByUsernameWithRoles(): void
    {
        $roleProvider = $this->createMock(UserRolesProviderInterface::class);
        $roleProvider->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_FOO']);
        $user = (new SecurityUserProvider($this->createRepository($this->createUser()), $this->createFactory(), $roleProvider))->loadUserByUsername('username');

        $this->assertSame('id', $user->getUsername());
        $this->assertSame(['ROLE_FOO'], $user->getRoles());
        $this->assertSame('', $user->getPassword());
        $this->assertNull($user->getSalt());
    }

    public function testLoadUserByUsernameWithUnknownUsername(): void
    {
        $provider = new SecurityUserProvider($this->createRepository(), $this->createFactory());

        $this->expectException(UsernameNotFoundException::class);

        $provider->loadUserByUsername('username');
    }

    public function testRefreshUser(): void
    {
        $provider = new SecurityUserProvider($this->createRepository($this->createUser()), $this->createFactory());
        $user = $provider->refreshUser($originUser = $provider->loadUserByUsername('username'));

        $this->assertEquals($originUser, $user);
        $this->assertNotSame($originUser, $user);
    }

    public function testRefreshUserWithUnknownUser(): void
    {
        $provider = new SecurityUserProvider($this->createRepository(), $this->createFactory());

        $this->expectException(UsernameNotFoundException::class);

        $provider->refreshUser(new SecurityUser($this->createUser()));
    }

    public function testRefreshUserWithUnsupportedUser(): void
    {
        $provider = new SecurityUserProvider($this->createRepository(), $this->createFactory());

        $this->expectException(UnsupportedUserException::class);

        $provider->refreshUser($this->createMock(UserInterface::class));
    }

    public function testSupportsClass(): void
    {
        $provider = new SecurityUserProvider($this->createRepository(), $this->createFactory());

        $this->assertTrue($provider->supportsClass(SecurityUser::class));
        $this->assertFalse($provider->supportsClass(UserInterface::class));
    }

    private function createFactory(): EntityAwareFactoryInterface
    {
        $factory = $this->createMock(EntityAwareFactoryInterface::class);
        $factory->expects($this->any())
            ->method('identify')
            ->willReturnCallback(function ($class, $id) {
                $userId = $this->createMock(UserIdInterface::class);
                $userId->expects($this->any())
                    ->method('toString')
                    ->willReturn($id);

                return $userId;
            });

        return $factory;
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
