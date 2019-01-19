<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command\ChangeUserAttributeValueCommand;
use MsgPhp\User\Event\UserAttributeValueChangedEvent;
use MsgPhp\User\Repository\UserAttributeValueRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChangeUserAttributeValueHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, UserAttributeValueRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(ChangeUserAttributeValueCommand $command): void
    {
        $userAttributeValue = $this->repository->find($command->attributeValueId);
        $oldValue = $userAttributeValue->getValue();
        $newValue = $command->value;

        if ($newValue === $oldValue) {
            return;
        }

        $userAttributeValue->changeValue($command->value);
        $this->repository->save($userAttributeValue);
        $this->dispatch(UserAttributeValueChangedEvent::class, compact('userAttributeValue', 'oldValue', 'newValue'));
    }
}
