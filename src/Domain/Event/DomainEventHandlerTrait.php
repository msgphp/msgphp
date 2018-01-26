<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Event;

use MsgPhp\Domain\Exception\UnknownDomainEventException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait DomainEventHandlerTrait
{
    public function handleEvent(DomainEventInterface $event): bool
    {
        $method = false === ($pos = strrpos($class = get_class($event), '\\')) ? $class : substr($class, $pos + 1);

        if ('DomainEvent' === substr($method, -11)) {
            $method = substr($method, 0, -11);
        }

        $method = 'handle'.ucfirst($method).'Event';

        if (!method_exists($this, $method)) {
            throw UnknownDomainEventException::createForHandler($this, $event);
        }

        return $this->$method($event);
    }
}
