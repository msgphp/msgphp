<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Event;

use MsgPhp\Domain\Exception\UnexpectedDomainEventException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait DomainEventHandlerTrait
{
    public function handleEvent(DomainEventInterface $event): bool
    {
        $method = 'handle'.(false === ($pos = strrpos($class = \get_class($event), '\\')) ? $class : substr($class, $pos + 1));
        if ('Event' !== substr($method, -5)) {
            $method .= 'Event';
        }

        if (!method_exists($this, $method)) {
            throw UnexpectedDomainEventException::createForHandler($this, $event);
        }

        return $this->{$method}($event);
    }
}
