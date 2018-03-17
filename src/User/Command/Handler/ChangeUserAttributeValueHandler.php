<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\Eav\Entity\AttributeValue;
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

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserAttributeValueRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(ChangeUserAttributeValueCommand $command): void
    {
        $userAttributeValue = $this->repository->find($this->factory->identify(AttributeValue::class, $command->attributeValueId));

        if ($command->value === $oldValue = $userAttributeValue->getValue()) {
            return;
        }

        $userAttributeValue->getAttributeValue()->changeValue($command->value);

        $this->repository->save($userAttributeValue);
        $this->dispatch(UserAttributeValueChangedEvent::class, [$userAttributeValue, $oldValue, $command->value]);
    }
}
