<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Command\Handler;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\GenericDomainObjectFactory;
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
            ->with(self::callback(static function (UserCreated $message): bool {
                /** @var TestUser $user */
                $user = $message->user;
                self::assertTrue($user->getId()->isEmpty());
                self::assertSame('value', $user->field);
                self::assertTrue($user->saved);
                self::assertSame(['field' => 'value', 'id' => $user->getId()], $message->context);

                return true;
            }))
        ;
        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (TestUser $entity): bool {
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
            ->with(self::callback(static function (UserCreated $message) use ($id): bool {
                /** @var TestUser $user */
                $user = $message->user;
                self::assertSame($id, $user->getId());
                self::assertNull($user->field);
                self::assertSame(['id' => $user->getId()], $message->context);

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
    /** @var mixed */
    public $field;
    /** @var bool */
    public $saved = false;
    private $id;

    /**
     * @param mixed $field
     */
    public function __construct(UserId $id, $field = null)
    {
        $this->id = $id;
        $this->field = $field;
    }

    public function getId(): UserId
    {
        return $this->id;
    }
}
