<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Security;

use MsgPhp\Domain\Infra\InMemory\GlobalObjectMemory;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Infra\InMemory\Repository\UserRepository;
use MsgPhp\User\Infra\Security\{SecurityUser, SecurityUserFactory};
use MsgPhp\User\UserIdInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

final class SecurityUserFactoryTest extends TestCase
{
    public function testIsAuthenticated(): void
    {
        $this->assertFalse($this->createFactory()->isAuthenticated());
        $this->assertFalse($this->createFactory('foo@bar.baz')->isAuthenticated());
        $this->assertFalse($this->createFactory('foo@bar.baz', ['ROLE_USER'])->isAuthenticated());

        $user = new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');

        $this->assertFalse($this->createFactory(new SecurityUser($user))->isAuthenticated());
        $this->assertTrue($this->createFactory(new SecurityUser($user), ['ROLE_USER'])->isAuthenticated());
    }

    public function testGetUserId(): void
    {
        $user = new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');
        $factory = $this->createFactory(new SecurityUser($user), ['ROLE_USER']);

        $this->assertSame($user->getId(), $factory->getUserId());
    }

    public function testGetUserIdWithNoToken(): void
    {
        $this->expectException(TokenNotFoundException::class);

        $this->createFactory()->getUserId();
    }

    public function testGetUserIdWithUnauthenticatedToken(): void
    {
        $this->expectException(AuthenticationExpiredException::class);

        $this->createFactory('foo@bar.baz')->getUserId();
    }

    public function testGetUserIdWithUnsupportedUser(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $this->createFactory('foo@bar.baz', ['ROLE_USER'])->getUserId();
    }

    public function testGetUser(): void
    {
        $user = new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');
        $repository = new UserRepository(User::class, new GlobalObjectMemory());
        $repository->save($user);

        $this->assertSame($user, $this->createFactory(new SecurityUser($user), ['ROLE_USER'], $repository)->getUser());
    }

    public function testGetUnknownUser(): void
    {
        $user = new User($this->createMock(UserIdInterface::class), 'foo@bar.baz', 'secret');

        $this->expectException(UsernameNotFoundException::class);

        $this->createFactory(new SecurityUser($user), ['ROLE_USER'])->getUser();
    }

    private function createFactory($securityUser = null, array $roles = [], UserRepository $repository = null): SecurityUserFactory
    {
        $storage = new TokenStorage();

        if (null !== $securityUser) {
            $storage->setToken(new UsernamePasswordToken($securityUser, '', 'provider_key', $roles));
        }

        return new SecurityUserFactory($storage, $repository ?? new UserRepository(User::class, new GlobalObjectMemory()));
    }
}
