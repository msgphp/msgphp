<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Security;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Infra\InMemory\Repository\UserRepository;
use MsgPhp\User\Infra\Security\SecurityUser;
use MsgPhp\User\Infra\Security\SecurityUserChecker;
use MsgPhp\User\UserIdInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityUserCheckerTest extends TestCase
{
    public function testPreAuth(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');
        $checker = new SecurityUserChecker(new UserRepository([$user], User::class));

        try {
            $checker->checkPreAuth($securityUser = new SecurityUser($user));

            $this->fail();
        } catch (DisabledException $e) {
            $this->assertTrue(true);
        }

        $user->enable();

        try {
            $checker->checkPreAuth($securityUser);

            $this->assertTrue(true);
        } catch (\Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testPreAuthWithUnknownUser(): void
    {
        $user = new User($this->getMockBuilder(UserIdInterface::class)->getMock(), 'foo@bar.baz', 'secret');

        $this->expectException(EntityNotFoundException::class);

        (new SecurityUserChecker(new UserRepository([], User::class)))
            ->checkPreAuth(new SecurityUser($user));
    }

    public function testPreAuthWithUnsupportedUser(): void
    {
        try {
            (new SecurityUserChecker(new UserRepository([], User::class)))
                ->checkPreAuth($this->getMockBuilder(UserInterface::class)->getMock());

            $this->assertTrue(true);
        } catch (\Throwable $e) {
            $this->fail($e->getMessage());
        }
    }
}
