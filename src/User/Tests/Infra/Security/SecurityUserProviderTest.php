<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Security;

use MsgPhp\User\Entity\User;
use MsgPhp\User\Infra\InMemory\Repository\UserRepository;
use MsgPhp\User\Infra\Security\SecurityUser;
use MsgPhp\User\Infra\Security\SecurityUserProvider;
use MsgPhp\User\Infra\Security\UserRoleProviderInterface;
use MsgPhp\User\UserIdInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityUserProviderTest extends TestCase
{
    public function testLoadUserByUsername(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');
        $repository = new UserRepository([$user], User::class);
        $securityUser = $this->createProvider(['ROLE_USER'], $repository)->loadUserByUsername($user->getEmail());

        $this->assertInstanceOf(SecurityUser::class, $securityUser);
        $this->assertSame($securityUser->getRoles(), ['ROLE_USER']);
        $this->assertSame($securityUser->getPassword(), $user->getPassword());
        $this->assertSame($securityUser->getUsername(), $user->getEmail());
    }

    public function testLoadUnknownUserByUsername(): void
    {
        $this->expectException(UsernameNotFoundException::class);

        $this->createProvider()->loadUserByUsername('foo@bar.baz');
    }

    public function testRefreshUser(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');
        $repository = new UserRepository([$user], User::class);
        $securityUser = $this->createProvider([], $repository)->refreshUser($oldSecurityUser = new SecurityUser($user));

        $this->assertEquals($oldSecurityUser, $securityUser);
        $this->assertNotSame($oldSecurityUser, $securityUser);
    }

    public function testRefreshUserWithInvalidId(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');

        $this->expectException(UsernameNotFoundException::class);

        $this->createProvider()->refreshUser(new SecurityUser($user));
    }

    public function testRefreshUnknownUser(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $this->createProvider()->refreshUser($this->getMockBuilder(UserInterface::class)->getMock());
    }

    public function testSupportsClass(): void
    {
        $this->assertTrue($this->createProvider()->supportsClass(SecurityUser::class));
        $this->assertFalse($this->createProvider()->supportsClass(UserInterface::class));
    }

    private function createProvider(array $roles = [], UserRepository $repository = null)
    {
        return new SecurityUserProvider($repository ?? new UserRepository([], User::class), new class($roles) implements UserRoleProviderInterface {
            private $roles;

            public function __construct(array $roles)
            {
                $this->roles = $roles;
            }

            public function getRoles(User $user): array
            {
                return $this->roles;
            }
        });
    }
}
