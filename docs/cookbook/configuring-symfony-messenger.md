# Configuring Symfony Messenger

To be able to dispatch messages provided by MsgPHP a [domain message bus](../message-driven/message-bus.md) must be
configured.

In this article is explained how to setup [Symfony Messenger infrastructure](../infrastructure/symfony-messenger.md).

## Installation

```bash
composer install symfony/messenger

# with Symfony Flex
composer install messenger
```

## Configuration

See the [recipe configuration] for the minimal configuration to put in `config/packages/messenger.yaml`.

!!! info
    The configuration is automatically added with Symfony Flex

### Configure a Command and Event Bus

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
                default_middleware: allow_no_handlers
                middleware:
                    - msgphp.messenger.console_message_receiver
```

By adding the `msgphp.messenger.console_message_receiver` middleware MsgPHP console commands are able to receive back a
dispatched message. This is recommended to provide better output in CLI.

### Enable the Command and Event Bus

MsgPHP uses the bus configured with `framework.messenger.default_bus` for both command and event messages by default. To
use your custom buses instead configure the bus aliases:

```yaml
# config/services.yaml

services:
    # ...

    msgphp.messenger.command_bus: '@command_bus'
    msgphp.messenger.event_bus: '@event_bus'
```

[recipe configuration]: https://github.com/symfony/recipes/blob/master/symfony/messenger/4.1/config/packages/messenger.yaml
