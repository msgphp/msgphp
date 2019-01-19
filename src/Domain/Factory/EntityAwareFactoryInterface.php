<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface EntityAwareFactoryInterface extends DomainObjectFactoryInterface
{
    /**
     * @return object
     */
    public function reference(string $class, $id);
}
