# Configuring A Message Bus

To be able to dispatch the default [messages](../reference/messages.md) a [message bus](../message-driven/message-bus.md)
must be configured.

## Using [Symfony Messenger](../infrastructure/symfony-messenger.md)

### Installation

```bash
composer install symfony/messenger

# with Symfony Flex
composer install messenger
```

### Configuration

See the Messenger [recipe configuration] for the minimal configuration to put in `config/packages/messenger.yaml`.

!!! info
    The configuration is automatically added with Symfony Flex

Configure a command- and event bus:

```yaml
# config/packages/messenger.yaml

framework:
    messenger:
        # ...

        default_bus: command_bus
        buses:
            command_bus:
                middleware:
                    - msgphp.messenger.console_message_receiver
            event_bus:
                middleware:
                    - msgphp.messenger.console_message_receiver
                    - allow_no_handler
```

By default MsgPHP uses the bus configured under `framework.messenger.default_bus`. You can override its alias services
to use any other bus:

```yaml
# config/services.yaml

services:
    msgphp.messenger.command_bus: '@command_bus'
    msgphp.messenger.event_bus: '@event_bus'

    # ...
```

## Using [SimpleBus](../infrastructure/simple-bus.md)

### Installation

```bash
composer install simple-bus/symfony-bridge
```

### Configuration

Enable the bundles:

```php
<?php

// config/bundles.php

return [
    // ...
    SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle::class => ['all' => true],
    SimpleBus\SymfonyBridge\SimpleBusEventBusBundle::class => ['all' => true],
];
```

By default MsgPHP uses the `simple_bus.command_bus` and `simple_bus.event_bus` services. You can override its alias 
services to use any other bus:

```yaml
# config/services.yaml

services:
    msgphp.simple_bus.command_bus: '@simple_bus.command_bus'
    msgphp.simple_bus.event_bus: '@simple_bus.event_bus'

    # ...
```

## Using A Custom Bus

To use a custom [message bus](../message-driven/message-bus.md) implementation you can override its main alias service:

```yaml
# config/services.yaml

services:
    MsgPhp\Domain\Message\DomainMessageBusInterface: '@my_bus'

    # ...
```

!!! info
    The implementation must be capable to dispatch both command- and event messages

[recipe configuration]: https://github.com/symfony/recipes/blob/master/symfony/messenger/4.1/config/packages/messenger.yaml
