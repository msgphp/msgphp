<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Event;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EventSourcingCommandHandlerTrait
{
    /**
     * @param object $target
     */
    private function handleEvent($target, DomainEvent $event): bool
    {
        if (!$target instanceof DomainEventHandler) {
            throw new \LogicException(sprintf('Event target "%s" must be an instance of "%s" to handle event "%s".', \get_class($target), DomainEventHandler::class, \get_class($event)));
        }

        return $target->handleEvent($event);
    }
}
