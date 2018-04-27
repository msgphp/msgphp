<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\SimpleBus\Middleware;

use MsgPhp\Domain\Infra\Console\MessageReceiver;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConsoleMessageReceiverMiddleware implements MessageBusMiddleware
{
    private $receiver;

    public function __construct(MessageReceiver $receiver)
    {
        $this->receiver = $receiver;
    }

    public function handle($message, callable $next): void
    {
        $this->receiver->receive($message);

        $next($message);
    }
}
