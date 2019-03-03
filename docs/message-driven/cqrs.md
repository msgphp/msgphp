# Command Query Responsibility Segregation

Commands are domain objects and provided per domain layer. They usually follow a [POPO] design. Its purpose is to
describe an action to be taken. For commands being messages they can be dispatched using any [message bus](message-bus.md).

## Event-Sourcing Command Handler

An event-sourcing command handler utility trait is provided by `MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait`.
Its purpose is to ease the handling of command messages by sourcing a [domain event](../event-sourcing/events.md) to its
[event handler](../event-sourcing/event-handlers.md).

- `handle(object $command, callable $onHandled = null): void`
    - `$command`: The command message to be handled
    - `$onHandled`: Callable to be invoked in case the triggered domain event is handled. It receives the event handler
      as first argument.
- `abstract getDomainEvent(object $command): DomainEventInterface`
- `abstract getDomainEventHandler(object $command): DomainEventHandlerInterface`

### Basic Example

```php
<?php

use MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait; 
use MsgPhp\Domain\Event\DomainEventHandlerInterface;
use MsgPhp\Domain\Event\DomainEventInterface;
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

    protected function getDomainEventHandler(MyCommand $command): DomainEventHandlerInterface
    {
        return new MyEntity();
    }
}

// --- USAGE ---

/** @var DomainMessageBusInterface $bus */
$bus = ...;

$bus->dispatch(new MyCommand());
```

[POPO]: https://stackoverflow.com/questions/41188002/what-does-the-term-plain-old-php-object-popo-exactly-mean
