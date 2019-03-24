<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Event;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait DomainEventHandlerTrait
{
    public function handleEvent(DomainEvent $event): bool
    {
        $method = 'handle'.(false === ($pos = strrpos($class = \get_class($event), '\\')) ? $class : substr($class, $pos + 1));
        if ('Event' !== substr($method, -5)) {
            $method .= 'Event';
        }

        if (!method_exists($this, $method)) {
            throw new \LogicException(sprintf('Domain event "%s" cannot be handled by "%s".', \get_class($event), \get_class($this)));
        }

        return $this->{$method}($event);
    }
}
