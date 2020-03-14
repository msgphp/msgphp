<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Command;

use MsgPhp\User\Command;
use MsgPhp\User\Event;
use MsgPhp\User\Tests\Fixtures\Entities\TestUser;
use MsgPhp\User\UserId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CreateUserTest extends TestCase
{
    use IntegrationTestTrait;

    public function testCreate(): void
    {
        self::$bus->dispatch(new Command\CreateUser([
            'email' => 'user@localhost',
            'password' => 'pa$$word',
            'foo' => 'bar',
        ]));

        self::assertMessageIsDispatchedOnce(Event\UserCreated::class);

        /** @var Event\UserCreated $event */
        $event = self::$dispatchedMessages[Event\UserCreated::class][0];
        /** @var TestUser $user */
        $user = $event->user;
        $repository = self::createUserRepository();

        self::assertSame('bar', $event->context['foo'] ?? null);
        self::assertInstanceOf(UserId::class, $event->context['id'] ?? null);
        self::assertTrue($event->context['id']->isNil());
        self::assertCount(1, $repository->findAll());
        self::assertSame($user, $repository->find($user->getId()));
        self::assertSame($user, $repository->findByUsername('user@localhost'));
        self::assertSame('user@localhost', $user->getEmail());
        self::assertSame('pa$$word', $user->getPassword());
    }

    public function testCreateWithId(): void
    {
        self::$bus->dispatch(new Command\CreateUser([
            'email' => 'user@localhost',
            'password' => 'pa$$word',
            'id' => $id = self::createDomainFactory()->create(UserId::class),
        ]));

        self::assertMessageIsDispatchedOnce(Event\UserCreated::class);

        /** @var Event\UserCreated $event */
        $event = self::$dispatchedMessages[Event\UserCreated::class][0];
        $repository = self::createUserRepository();

        self::assertSame($id, $event->context['id'] ?? null);
        self::assertFalse($event->user->getId()->isNil());
        self::assertTrue($repository->exists($event->user->getId()));
        self::assertTrue($repository->usernameExists('user@localhost'));
    }
}
