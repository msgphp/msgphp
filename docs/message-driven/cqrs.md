# Command Query Responsibility Segregation

Commands are domain objects and provided per domain layer. They usually follow a [POPO](https://stackoverflow.com/questions/41188002/what-does-the-term-plain-old-php-object-popo-exactly-mean)
design. Its purpose is to describe an action to be taken. For commands being messages they can be dispatched using any
[message bus](domain-message-bus.md).

## Command handlers

The message bus resolves a command handler, which in turn handles the command. Thus performs the requested action.
Usually a command handler is designed, but not limited, to handle one specific command message.

## Implementations

### `MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait`

Handles a command message by sourcing a domain event.

- `handle(object $command, callable $onHandled = null): void`
    - If the domain event is handled `$onHandled` will be invoked (if given), receiving the handler as first argument
- `abstract getDomainEvent(object $command): DomainEventInterface`
    - The [domain event](../event-sourcing/domain-events.md) to be handled
- `abstract getDomainEventHandler(object $command): DomainEventHandlerInterface`
    - The [domain event handler](../event-sourcing/domain-event-handlers.md) handling the domain event

## Generic example

```php
<?php

class MyCommand { }
class MyCommandHandler
{
    public function __invoke(MyCommand $command): void
    {
        // do something
    }
}

$bus->dispatch(new MyCommand());
```

## Event sourcing example

```php
<?php

use MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait; 
use MsgPhp\Domain\Event\{DomainEventHandlerInterface, DomainEventInterface}; 

class MyCommand { }
class MyCommandHandler
{
    use EventSourcingCommandHandlerTrait;

    public function __invoke(MyCommand $command): void
    {
        $this->doHandle($command, function (MyEntity $entity): void {
            // do something
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

class MyDomainEvent implements DomainEventInterface { }
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

$bus->dispatch(new MyCommand());
```
