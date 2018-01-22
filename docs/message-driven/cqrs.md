# Command Query Responsibility Segregation

Command domain objects are provided per domain layer and usually follow a [POPO](https://stackoverflow.com/questions/41188002/what-does-the-term-plain-old-php-object-popo-exactly-mean)
design. It's a specific message type that can be dispatched using any [message bus](domain-message-bus.md).

## Command handlers

A command message is handled by a _command handler_. It's designed, but not limited, to handle one specific command
message.

## Implementations

### `MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait`

Sources a [domain event](../event-sourcing/domain-events.md) from a command message.

- `doHandle($command, callable $onHandled = null): void`
    - Invokes the domain event
    - If the domain event is handled `$onHandled`, if given, is invoked receiving the handler as first argument
- `abstract getDomainEvent(object $message): DomainEventInterface`
    - Get the domain event to be handled
- `abstract getDomainEventHandler($message)`
    - Get the [domain event handler](../event-sourcing/domain-event-handlers.md) (should be type
    `DomainEventHandlerInterface`)

## Generic example

```php
<?php

class MyCommand { }
class MyCommandHandler
{
    public function handle(MyCommand $command): void
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

    public function handle(MyCommand $command): void
    {
        $this->doHandle($command, function (MyEntity $entity): void {
            // do something, e.g. save entity using a repository
        });
    }
    
    protected function getDomainEvent(MyCommand $command): DomainEventInterface
    {
        return new MyDomainEvent();
    }
    
    protected function getDomainEventHandler(MyCommand $command)
    {
        // usually the command provides e.g. an entity identity to be looked up by a repository
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
