# Events

A domain event is bound to `MsgPhp\Domain\Event\DomainEventInterface`. Its purpose is to represent any action that can
 _happen_ regarding the domain. When handled it might lead to an application state change.

## API

!!! note
    This is a marker interface and has no default API

## Implementations

- `MsgPhp\Domain\Event\ConfirmEvent`
- `MsgPhp\Domain\Event\DisableEvent`
- `MsgPhp\Domain\Event\EnableEvent`

## Basic Example

```php
<?php

use MsgPhp\Domain\Entity\Features\CanBeEnabled;
use MsgPhp\Domain\Event\{DomainEventHandlerInterface, DomainEventHandlerTrait, EnableEvent};

// --- SETUP ---

class MyEntity implements DomainEventHandlerInterface
{
    use CanBeEnabled;
    use DomainEventHandlerTrait;
}

// --- USAGE ---

$entity = new MyEntity();
$entity->isEnabled(); // false
$entity->handleEvent(new EnableEvent()); // true
$entity->handleEvent(new EnableEvent()); // false
$entity->isEnabled(); // true
```

!!! note
    Because `CanBeEnabled` defines `handleEnableEvent(EnableEvent $event)` it's detected in `DomainEventHandlerTrait::handleEvent()`
    by convention
