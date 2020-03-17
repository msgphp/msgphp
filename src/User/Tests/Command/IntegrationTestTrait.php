<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Command;

use Doctrine\ORM\Events;
use MsgPhp\Domain\Factory\GenericDomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Doctrine\Event\ObjectMappingListener;
use MsgPhp\Domain\Infrastructure\Doctrine\MappingConfig;
use MsgPhp\Domain\Infrastructure\Doctrine\Test\EntityManagerTestTrait;
use MsgPhp\Domain\Infrastructure\Messenger\Test\MessageBusTestTrait;
use MsgPhp\User\Command;
use MsgPhp\User\Infrastructure\Doctrine\Repository;
use MsgPhp\User\Infrastructure\Doctrine\UserObjectMappings;
use MsgPhp\User\Role;
use MsgPhp\User\ScalarUserId;
use MsgPhp\User\Tests\Fixtures\Entities;
use MsgPhp\User\User;
use MsgPhp\User\UserEmail;
use MsgPhp\User\UserId;
use MsgPhp\User\Username;
use MsgPhp\User\UserRole;

trait IntegrationTestTrait
{
    use EntityManagerTestTrait;
    use MessageBusTestTrait;

    /**
     * @beforeClass
     */
    public static function configureEm(): void
    {
        self::$em->getEventManager()->addEventListener(Events::loadClassMetadata, new ObjectMappingListener(
            [new UserObjectMappings()],
            new MappingConfig([], ['key_max_length' => 255]),
            self::getClassMapping()
        ));
    }

    protected static function getMessageHandlers(): iterable
    {
        $factory = self::createDomainFactory();
        $bus = self::createDomainMessageBus();
        $roleRepository = self::createRoleRepository();
        $userRepository = self::createUserRepository();

        yield Command\CreateRole::class => new Command\Handler\CreateRoleHandler($factory, $bus, $roleRepository);
        yield Command\CreateUser::class => new Command\Handler\CreateUserHandler($factory, $bus, $userRepository);
        yield Command\DeleteRole::class => new Command\Handler\DeleteRoleHandler($factory, $bus, $roleRepository);
        yield Command\DeleteUser::class => new Command\Handler\DeleteUserHandler($factory, $bus, $userRepository);
    }

    protected static function getClassMapping(): array
    {
        return [
            UserId::class => ScalarUserId::class,
            Role::class => Entities\TestRole::class,
            User::class => Entities\TestUser::class,
            Username::class => Entities\TestUsername::class,
            UserEmail::class => Entities\TestUserEmail::class,
            UserRole::class => Entities\TestUserRole::class,
        ];
    }

    protected static function createSchema(): bool
    {
        return true;
    }

    protected static function getEntityMappings(): iterable
    {
        yield 'annot' => [
            'MsgPhp\\User\\Tests\\Fixtures\\Entities\\' => \dirname(__DIR__).'/Fixtures/Entities',
        ];
        yield 'xml' => [
            'MsgPhp' => self::createEntityDistMapping(\dirname(__DIR__, 2).'/Infrastructure/Doctrine/Resources/dist-mapping'),
        ];
    }

    protected static function getEntityIdTypes(): iterable
    {
        return [];
    }

    private static function createDomainFactory(): DomainObjectFactory
    {
        return new DomainObjectFactory(new GenericDomainObjectFactory(self::getClassMapping()), self::$em);
    }

    private static function createRoleRepository(): Repository\RoleRepository
    {
        return new Repository\RoleRepository(Entities\TestRole::class, self::$em);
    }

    private static function createUserRepository(): Repository\UserRepository
    {
        return new Repository\UserRepository(Entities\TestUser::class, self::$em, 'credential.email');
    }
}
