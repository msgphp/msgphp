<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Message;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class NoopMessageHandler
{
    /**
     * @param object $message
     */
    public function __invoke($message): void
    {
    }
}
