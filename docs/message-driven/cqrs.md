# Command Query Responsibility Segregation

Commands are a type of message objects and provided per domain layer. Its purpose is to describe an action to be taken
within its domain. As such the domain layer can be consumed and operated by dispatching commands.

## Event-Sourcing Command Handler

An event-sourcing command handler trait is provided by `MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait`. Its
purpose is to ease the handling of command messages by sourcing a [domain event](../event-sourcing/events.md) to a
[event handler](../event-sourcing/event-handlers.md), derived from the command message.

### Basic Example

```php
<?php

use MsgPhp\Domain\Event\DomainEventHandlerInterface;
use MsgPhp\Domain\Event\DomainEventInterface;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Message\DomainMessageBusInterface;

// --- SETUP ---

class MyCommand
{
}

class MyDomainEvent implements DomainEventInterface
{
}

class MyEntity implements DomainEventHandlerInterface
{
    public function handleEvent(DomainEventInterface $event): bool
    {
        if ($event instanceof MyDomainEvent) {
            // do something

            return true;
        }

        return false;
    }
}

class MyCommandHandler
{
    use EventSourcingCommandHandlerTrait;

    public function __invoke(MyCommand $command): void
    {
        $this->handle($command, function (MyEntity $entity): void {
            // do something when $command is handled
        });
    }

    protected function getDomainEvent(MyCommand $command): DomainEventInterface
    {
        return new MyDomainEvent();
    }

    protected function getDomainEventTarget(MyCommand $command): MyEntity
    {
        return new MyEntity();
    }
}

// --- USAGE ---

/** @var DomainMessageBusInterface $bus */
$bus = ...;

$bus->dispatch(new MyCommand());
```
