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
     * @psalm-suppress ImplementedReturnTypeMismatch
     *
     * @param object $command
     *
     * @return object
     */
    abstract protected function getDomainEventTarget($command);

    /**
     * @param object $command
     */
    private function handle($command, callable $onHandled = null): void
    {
        /** @psalm-suppress TypeCoercion */
        $event = $this->getDomainEvent($command);
        /** @psalm-suppress TypeCoercion */
        $target = $this->getDomainEventTarget($command);

        if (!$target instanceof DomainEventHandlerInterface) {
            throw new \LogicException(sprintf('Event target "%s" must be an instance of "%s" to handle event "%s".', \get_class($target), DomainEventHandlerInterface::class, \get_class($event)));
        }

        if ($target->handleEvent($event) && null !== $onHandled) {
            $onHandled($target);
        }
    }
}
