<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\SimpleBus;

use MsgPhp\Domain\Infra\Console\MessageSubscriber;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConsoleSubscriberMiddleware implements MessageBusMiddleware
{
    private $subscriber;

    public function __construct(MessageSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * @param object $message
     */
    public function handle($message, callable $next): void
    {
        $next($message);
        ($this->subscriber)($message);
    }
}
