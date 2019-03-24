<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Message;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
interface MessageReceiving
{
    /**
     * @param object $message
     */
    public function onMessageReceived($message): void;
}
