<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\User\{ScalarUserId, UserIdInterface};
use MsgPhp\User\Command\CreateUserCommand;
use MsgPhp\User\Command\Handler\CreateUserHandler;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Event\UserCreatedEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CreateUserHandlerTest extends TestCase
{
    public function testHandler(): void
    {
        $bus = $this->createMock(DomainMessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (UserCreatedEvent $message): bool {
                self::assertInstanceOf(TestUser::class, $message->user);
                self::assertTrue($message->user->getId()->isEmpty());
                self::assertSame('value', $message->user->field);
                self::assertTrue($message->user->saved);
                self::assertSame(['field' => 'value', 'id' => $message->user->getId()], $message->context);

                return true;
            }))
        ;
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (TestUser $entity): bool {
                $entity->saved = true;

                return true;
            }))
        ;
        (new CreateUserHandler(self::createFactory(), $bus, $repository))(new CreateUserCommand([
            'field' => 'value',
        ]));
    }

    public function testHandlerWithId(): void
    {
        $id = $this->createMock(UserIdInterface::class);
        $bus = $this->createMock(DomainMessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (UserCreatedEvent $message) use ($id): bool {
                self::assertInstanceOf(TestUser::class, $message->user);
                self::assertSame($id, $message->user->getId());
                self::assertNull($message->user->field);
                self::assertSame(['id' => $message->user->getId()], $message->context);

                return true;
            }))
        ;
        $repository = $this->createMock(UserRepositoryInterface::class);
        (new CreateUserHandler(self::createFactory(), $bus, $repository))(new CreateUserCommand([
            'id' => $id,
        ]));
    }

    private static function createFactory(): DomainObjectFactory
    {
        return new DomainObjectFactory([
            UserIdInterface::class => ScalarUserId::class,
            User::class => TestUser::class,
        ]);
    }
}

class TestUser extends User
{
    public $field;
    public $saved = false;

    private $id;

    public function __construct($id, $field = null)
    {
        $this->id = $id;
        $this->field = $field;
    }

    public function getId(): UserIdInterface
    {
        return $this->id;
    }
}
