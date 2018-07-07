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

        default_bus: command
        buses:
            command: ~
            event:
                middleware: [logging, allow_no_handler, route_messages, call_message_handler]

YAML;
