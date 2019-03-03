<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Event;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EventSourcingCommandHandlerTrait
{
    /**
     * @param object $command
     */
    abstract protected function getDomainEvent($command): DomainEventInterface;

    /**
     * @param object $command
     */
    abstract protected function getDomainEventHandler($command): DomainEventHandlerInterface;

    /**
     * @param object $command
     */
    private function handle($command, callable $onHandled = null): void
    {
        /** @psalm-suppress TypeCoercion */
        $event = $this->getDomainEvent($command);
        /** @psalm-suppress TypeCoercion */
        $handler = $this->getDomainEventHandler($command);

        if ($handler->handleEvent($event) && null !== $onHandled) {
            $onHandled($handler);
        }
    }
}
