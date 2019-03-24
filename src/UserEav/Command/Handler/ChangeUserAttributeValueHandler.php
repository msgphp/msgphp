<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\ChangeUserAttributeValue;
use MsgPhp\User\Event\UserAttributeValueChanged;
use MsgPhp\User\Repository\UserAttributeValueRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChangeUserAttributeValueHandler
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

    public function __invoke(ChangeUserAttributeValue $command): void
    {
        $userAttributeValue = $this->repository->find($command->attributeValueId);
        $oldValue = $userAttributeValue->getValue();
        $newValue = $command->value;

        if ($newValue === $oldValue) {
            return;
        }

        $userAttributeValue->changeValue($command->value);
        $this->repository->save($userAttributeValue);
        $this->dispatch(UserAttributeValueChanged::class, compact('userAttributeValue', 'oldValue', 'newValue'));
    }
}
