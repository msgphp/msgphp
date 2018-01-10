<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

use MsgPhp\Domain\Exception\InvalidEntityClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait AbstractDomainEntityRepositoryTrait
{
    private $class;
    private $identityMap;

    public function __construct(string $class, DomainIdentityMapInterface $identityMap)
    {
        $this->class = $class;
        $this->identityMap = $identityMap;
    }

    private function normalizeIdentifier($id)
    {
        if ($id instanceof DomainIdInterface) {
            return $id->isEmpty() ? null : $id;
        }

        if (is_object($id)) {
            try {
                return $this->identityMap->getIdentity($id) ? $id : null;
            } catch (InvalidEntityClassException $e) {
                return $id;
            }
        }

        return $id;
    }

    private function toIdentity($id, ...$idN): ?array
    {
        if (count($ids = func_get_args()) !== count($this->identityMap->getIdentifierFieldNames($this->class))) {
            return null;
        }

        foreach ($ids as $id) {
            if (null === $this->normalizeIdentifier($id)) {
                return null;
            }
        }

        return array_combine($this->identityMap->getIdentifierFieldNames($this->class), $ids);
    }

    public function isIdentity(array $fields): bool
    {
        $idFields = $this->identityMap->getIdentifierFieldNames($this->class);

        return count($fields) === count($idFields) && [] === array_diff(array_keys($fields), $idFields);
    }
}
