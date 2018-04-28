# Symfony Messenger

An overview of available infrastructural code when using [Symfony Messenger][messenger-project].

- Requires [symfony/messenger]

## Domain Message Bus

A Symfony Messenger tailored [domain message bus](../message-driven/message-bus.md) is provided by `MsgPhp\Domain\Infra\Messenger\DomainMessageBus`.
It decorates any `Symfony\Component\Messenger\MessageBusInterface` type.

- `__construct(MessageBusInterface $bus)`
    - `$bus`: The decorated bus

### Basic example

```php
<?php

use MsgPhp\Domain\Infra\Messenger\DomainMessageBus;
use Symfony\Component\Messenger\MessageBusInterface;

// --- SETUP ---

/** @var MessageBusInterface $bus */
$bus = ...;
$domainBus = new DomainMessageBus($bus);

// --- USAGE ---

$result = $domainBus->dispatch(new SomeMessage());
```

[messenger-project]: https://symfony.com/doc/current/components/messenger.html
[symfony/messenger]: https://packagist.org/packages/symfony/messenger
