<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Command;

use MsgPhp\Domain\Event\{DomainEventInterface, DomainEventHandlerInterface};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EventSourcingCommandHandlerTrait
{
    /**
     * @param object $command
     */
    protected function doHandle($command, callable $onHandled = null): void
    {
        $event = $this->getDomainEvent($command);
        $handler = $this->getDomainEventHandler($command);

        if (!$handler instanceof DomainEventHandlerInterface) {
            throw new \LogicException(sprintf('Object "%s" is unable to handle domain event "%s". Did you forgot to implement DomainEventHandlerInterface?', get_class($handler), get_class($event)));
        }

        if ($handler->handleEvent($event) && null !== $onHandled) {
            $onHandled($handler);
        }
    }

    /**
     * @param object $command
     */
    abstract protected function getDomainEvent($command): DomainEventInterface;

    /**
     * @param object $command
     */
    abstract protected function getDomainEventHandler($command): DomainEventHandlerInterface;
}
