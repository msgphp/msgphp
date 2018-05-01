<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\Eav\Entity\AttributeValue;
use MsgPhp\User\Command\DeleteUserAttributeValueCommand;
use MsgPhp\User\Event\UserAttributeValueDeletedEvent;
use MsgPhp\User\Repository\UserAttributeValueRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserAttributeValueHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserAttributeValueRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(DeleteUserAttributeValueCommand $command): void
    {
        try {
            $userAttributeValue = $this->repository->find($this->factory->identify(AttributeValue::class, $command->id));
        } catch (EntityNotFoundException $e) {
            return;
        }

        $this->repository->delete($userAttributeValue);
        $this->dispatch(UserAttributeValueDeletedEvent::class, [$userAttributeValue]);
    }
}
