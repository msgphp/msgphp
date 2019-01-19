<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\DomainIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface EntityAwareFactoryInterface extends DomainObjectFactoryInterface
{
    /**
     * @return object
     */
    public function reference(string $class, $id);

    public function identify(string $class, $value): DomainIdInterface;
}
