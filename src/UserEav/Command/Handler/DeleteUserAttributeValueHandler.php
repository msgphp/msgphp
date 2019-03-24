<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\DeleteUserAttributeValue;
use MsgPhp\User\Event\UserAttributeValueDeleted;
use MsgPhp\User\Repository\UserAttributeValueRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserAttributeValueHandler
{
    use MessageDispatchingTrait;

    /**
     * @var UserAttributeValueRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserAttributeValueRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(DeleteUserAttributeValue $command): void
    {
        try {
            $userAttributeValue = $this->repository->find($command->attributeValueId);
        } catch (EntityNotFoundException $e) {
            return;
        }

        $this->repository->delete($userAttributeValue);
        $this->dispatch(UserAttributeValueDeleted::class, compact('userAttributeValue'));
    }
}
