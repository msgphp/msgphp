<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface EventBusInterface
{
    /**
     * @param object $message
     */
    public function handle($message): void;
}
