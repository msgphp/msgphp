<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Messenger;

use MsgPhp\Domain\Infra\Console\MessageSubscriber;
use Symfony\Component\Messenger\MiddlewareInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConsoleSubscriberMiddleware implements MiddlewareInterface
{
    private $subscriber;

    public function __construct(MessageSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * @param object $message
     */
    public function handle($message, callable $next)
    {
        $result = $next($message);
        ($this->subscriber)($message);

        return $result;
    }
}
