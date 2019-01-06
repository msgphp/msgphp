<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DomainIdentityMappingInterface
{
    /**
     * @return string[]
     */
    public function getIdentifierFieldNames(string $class): array;

    /**
     * @param object $object
     */
    public function getIdentifiers($object): array;
}
