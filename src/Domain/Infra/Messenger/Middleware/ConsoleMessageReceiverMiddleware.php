<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Messenger\Middleware;

use MsgPhp\Domain\Infra\Console\MessageReceiver;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConsoleMessageReceiverMiddleware implements MiddlewareInterface
{
    private $receiver;

    public function __construct(MessageReceiver $receiver)
    {
        $this->receiver = $receiver;
    }

    public function handle($message, callable $next)
    {
        $this->receiver->receive($message);

        return $next($message);
    }
}
