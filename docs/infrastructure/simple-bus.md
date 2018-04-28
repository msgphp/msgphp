# SimpleBus

An overview of available infrastructural code when using [SimpleBus].

- Requires [simple-bus/message-bus]

## Domain Message Bus

A SimpleBus tailored [domain message bus](../message-driven/message-bus.md) is provided by `MsgPhp\Domain\Infra\SimpleBus\DomainMessageBus`.
It decorates any `SimpleBus\Message\Bus\MessageBus` type.

- `__construct(MessageBus $bus)`
    - `$bus`: The decorated bus

### Basic example

```php
<?php

use MsgPhp\Domain\Infra\SimpleBus\DomainMessageBus;
use SimpleBus\Message\Bus\MessageBus

// --- SETUP ---

/** @var MessageBus $bus */
$bus = ...;
$domainBus = new DomainMessageBus($bus);

// --- USAGE ---

$result = $domainBus->dispatch(new SomeMessage());
```

[SimpleBus]: http://docs.simplebus.io
[simple-bus/message-bus]: https://packagist.org/packages/simple-bus/message-bus
