# Symfony Messenger

An overview of available infrastructural code when using [Symfony Messenger][messenger-project].

- Requires [symfony/messenger]

## Domain Message Bus

A Symfony Messenger tailored [domain message bus](../message-driven/message-bus.md) is provided by `MsgPhp\Domain\Infra\Messenger\DomainMessageBus`.

### Basic Example

```php
<?php

use MsgPhp\Domain\Infra\Messenger\DomainMessageBus;
use Symfony\Component\Messenger\MessageBusInterface;

// --- SETUP ---

class CommandMessage
{
}

class EventMessage
{
}

/** @var MessageBusInterface $commandBus */
$commandBus = ...;
/** @var MessageBusInterface $commandBus */
$eventBus = ...;
$eventClasses = [EventMessage::class];

$domainBus = new DomainMessageBus($commandBus, $eventBus, $eventClasses);

// --- USAGE ---

$domainBus->dispatch(new CommandMessage());
$domainBus->dispatch(new EventMessage());
```

[messenger-project]: https://symfony.com/doc/current/components/messenger.html
[symfony/messenger]: https://packagist.org/packages/symfony/messenger
