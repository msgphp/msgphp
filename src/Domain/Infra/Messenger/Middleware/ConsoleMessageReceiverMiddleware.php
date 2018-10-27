<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Messenger\Middleware;

use MsgPhp\Domain\Infra\Console\MessageReceiver;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

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

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->receiver->receive($envelope->getMessage());

        return $stack->next()->handle($envelope, $stack);
    }
}
