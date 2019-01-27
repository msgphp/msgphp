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

    /**
     * @param object $command
     */
    abstract protected function getDomainEvent($command): DomainEventInterface;

    /**
     * @param object $command
     */
    abstract protected function getDomainEventHandler($command): DomainEventHandlerInterface;
}
