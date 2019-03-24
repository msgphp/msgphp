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

use MsgPhp\Domain\Event\DomainEvent;
use MsgPhp\Domain\Event\DomainEventHandler;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Message\DomainMessageBus;

// --- SETUP ---

class MyCommand
{
}

class MyDomainEvent implements DomainEvent
{
}

class MyEntity implements DomainEventHandler
{
    public function handleEvent(DomainEvent $event): bool
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

    protected function getDomainEvent(MyCommand $command): DomainEvent
    {
        return new MyDomainEvent();
    }

    protected function getDomainEventTarget(MyCommand $command): MyEntity
    {
        return new MyEntity();
    }
}

// --- USAGE ---

/** @var DomainMessageBus $bus */
$bus = ...;

$bus->dispatch(new MyCommand());
```
