<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Entity;

use MsgPhp\Domain\DomainIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface EntityFactoryInterface
{
    /**
     * @return object
     */
    public function create(string $entity, array $context = []);

    public function identify(string $entity, $id): DomainIdInterface;

    public function nextIdentity(string $entity): DomainIdInterface;
}
