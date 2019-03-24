<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Command\Handler;

use MsgPhp\Domain\Factory\GenericDomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\User\Command\CreateUser;
use MsgPhp\User\Command\Handler\CreateUserHandler;
use MsgPhp\User\Event\UserCreated;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\ScalarUserId;
use MsgPhp\User\User;
use MsgPhp\User\UserId;
use PHPUnit\Framework\TestCase;

final class CreateUserHandlerTest extends TestCase
{
    public function testHandler(): void
    {
        $bus = $this->createMock(DomainMessageBus::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (UserCreated $message): bool {
                self::assertInstanceOf(TestUser::class, $message->user);
                self::assertTrue($message->user->getId()->isEmpty());
                self::assertSame('value', $message->user->field);
                self::assertTrue($message->user->saved);
                self::assertSame(['field' => 'value', 'id' => $message->user->getId()], $message->context);

                return true;
            }))
        ;
        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (TestUser $entity): bool {
                $entity->saved = true;

                return true;
            }))
        ;
        (new CreateUserHandler(self::createFactory(), $bus, $repository))(new CreateUser([
            'field' => 'value',
        ]));
    }

    public function testHandlerWithId(): void
    {
        $id = $this->createMock(UserId::class);
        $bus = $this->createMock(DomainMessageBus::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (UserCreated $message) use ($id): bool {
                self::assertInstanceOf(TestUser::class, $message->user);
                self::assertSame($id, $message->user->getId());
                self::assertNull($message->user->field);
                self::assertSame(['id' => $message->user->getId()], $message->context);

                return true;
            }))
        ;
        $repository = $this->createMock(UserRepository::class);
        (new CreateUserHandler(self::createFactory(), $bus, $repository))(new CreateUser([
            'id' => $id,
        ]));
    }

    private static function createFactory(): GenericDomainObjectFactory
    {
        return new GenericDomainObjectFactory([
            UserId::class => ScalarUserId::class,
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

    public function getId(): UserId
    {
        return $this->id;
    }
}
