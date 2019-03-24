<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Message;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DomainMessageBus
{
    /**
     * @param object $message
     */
    public function dispatch($message): void;
}
