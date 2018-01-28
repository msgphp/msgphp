# Domain event handlers

A domain event handler is bound to `MsgPhp\Domain\Event\DomainEventHandlerInterface`. Its purpose is to handle
[domain events](domain-events.md). By convention a trait implementation is provided to map concrete events to
specialized handling methods.

## Implementations

- `MsgPhp\Domain\Event\DomainEventHandlerTrait`
    - Maps `<Type>DomainEvent` to `handle<Type>Event()`

## API

### `handleEvent(DomainEventInterface $event): bool`

Handles the given domain event for a known subject. A boolean return value tells a domain event is actually handled yes
or no.

## Generic example

```php
<?php

use MsgPhp\Domain\Event\DomainEventHandlerInterface;
use MsgPhp\Domain\Event\DomainEventHandlerTrait;

class MyDomainEvent
{
    public $newValue;
    
    public function __construct($value)
    {
        $this->newValue = $value;
    }
}

class MyEntity implements DomainEventHandlerInterface
{
    use DomainEventHandlerTrait;
    
    public $value;
    
    private function handleMyEvent(MyDomainEvent $event): bool
    {
        if ($this->value === $event->newValue) {
            return false;
        }

        $this->value = $event->newValue;
        
        return true;
    }
    
}

$entity = new MyEntity();
if ($entity->handleEvent(new MyDomainEvent('value'))) {
    // do something
}
```
