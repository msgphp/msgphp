<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\DomainIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface EntityFactoryInterface extends DomainObjectFactoryInterface
{
    public function identify(string $class, $id): DomainIdInterface;

    public function nextIdentity(string $class): DomainIdInterface;
}
