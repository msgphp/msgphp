<?php

declare(strict_types=1);

return <<<YAML
framework:
    messenger:
        transports:
            # Uncomment the following line to enable a transport named "amqp"
            # amqp: '%env(MESSENGER_TRANSPORT_DSN)%'

        routing:
            # Route your messages to the transports
            # 'App\Message\YourMessage': amqp

        default_bus: command_bus
        buses:
            command_bus: ~
            event_bus:
                middleware: [allow_no_handler]

YAML;
