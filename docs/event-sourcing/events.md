# Events

A domain event is bound to `MsgPhp\Domain\Event\DomainEvent`. Its purpose is to represent any action that can _happen_
regarding the domain. When handled it might lead to an application state change.

## API

!!! note
    This is a marker interface and has no default API

## Implementations

- `MsgPhp\Domain\Event\Confirm`
- `MsgPhp\Domain\Event\Disable`
- `MsgPhp\Domain\Event\Enable`

## Basic Example

```php
<?php

use MsgPhp\Domain\Model\CanBeEnabled;
use MsgPhp\Domain\Event\Enable;
use MsgPhp\Domain\Event\DomainEventHandler;
use MsgPhp\Domain\Event\DomainEventHandlerTrait;

// --- SETUP ---

class MyEntity implements DomainEventHandler
{
    use CanBeEnabled;
    use DomainEventHandlerTrait;
}

// --- USAGE ---

$entity = new MyEntity();
$entity->isEnabled(); // false
$entity->handleEvent(new Enable()); // true
$entity->handleEvent(new Enable()); // false
$entity->isEnabled(); // true
```

!!! note
    Because `CanBeEnabled` defines `handleEnableEvent(EnableEvent $event)` it's detected in `DomainEventHandlerTrait::handleEvent()`
    by convention
