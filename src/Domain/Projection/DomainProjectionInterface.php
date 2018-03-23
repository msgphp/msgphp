<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DomainProjectionInterface
{
    public static function fromDocument(array $document): self;
}
