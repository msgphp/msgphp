<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DomainIdentityMapInterface
{
    /**
     * @return string[]
     */
    public function getIdentifierFieldNames(string $class): array;

    /**
     * @param object $entity
     */
    public function getIdentity($entity): array;
}
