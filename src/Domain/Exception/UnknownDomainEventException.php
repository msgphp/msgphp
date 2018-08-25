<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Exception;

use MsgPhp\Domain\Event\{DomainEventHandlerInterface, DomainEventInterface};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UnknownDomainEventException extends \RuntimeException implements DomainExceptionInterface
{
    public static function createForHandler(DomainEventHandlerInterface $handler, DomainEventInterface $event): self
    {
        return new self(sprintf('Domain event "%s" cannot be handled by "%s".', \get_class($event), \get_class($handler)));
    }
}
