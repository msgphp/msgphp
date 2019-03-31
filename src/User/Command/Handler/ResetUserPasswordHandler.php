<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\ChangeUserCredential;
use MsgPhp\User\Command\ResetUserPassword;
use MsgPhp\User\Credential\PasswordProtectedCredential;
use MsgPhp\User\Event\Domain\FinishPasswordRequest;
use MsgPhp\User\Event\UserPasswordRequestFinished;
use MsgPhp\User\Repository\UserRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ResetUserPasswordHandler
{
    use EventSourcingCommandHandlerTrait;
    use MessageDispatchingTrait;

    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(ResetUserPassword $command): void
    {
        $oldCredential = $this->repository->find($command->userId)->getCredential();

        if ($oldCredential instanceof PasswordProtectedCredential) {
            $userId = $command->userId;
            $fields = [$oldCredential::getPasswordField() => $command->password] + $command->context;

            $this->dispatch(ChangeUserCredential::class, compact('userId', 'fields'));
        }

        $user = $this->repository->find($command->userId);

        if ($this->handleEvent($user, $this->factory->create(FinishPasswordRequest::class, compact('oldCredential')))) {
            $this->repository->save($user);
            $this->dispatch(UserPasswordRequestFinished::class, compact('user', 'oldCredential'));
        }
    }
}
