<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

use MsgPhp\Domain\Exception\InvalidClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainIdentityHelper
{
    private $mapping;

    public function __construct(DomainIdentityMappingInterface $mapping)
    {
        $this->mapping = $mapping;
    }

    public function isIdentifier($value): bool
    {
        if ($value instanceof DomainIdInterface) {
            return true;
        }

        if (is_object($value)) {
            try {
                if ($this->mapping->getIdentifierFieldNames(get_class($value))) {
                    return true;
                }
            } catch (InvalidClassException $e) {
            }
        }

        return false;
    }

    public function isEmptyIdentifier($value): bool
    {
        if (null === $value || ($value instanceof DomainIdInterface && $value->isEmpty())) {
            return true;
        }

        if (is_object($value)) {
            try {
                if (!$this->mapping->getIdentity($value)) {
                    return true;
                }
            } catch (InvalidClassException $e) {
            }
        }

        return false;
    }

    public function normalizeIdentifier($value)
    {
        if ($value instanceof DomainIdInterface) {
            return $value->isEmpty() ? null : $value->toString();
        }

        if (is_object($value)) {
            try {
                if (!$identity = $this->mapping->getIdentity($value)) {
                    return null;
                }
            } catch (InvalidClassException $e) {
                return $value;
            }

            $identity = array_map(function ($id) {
                return $this->normalizeIdentifier($id);
            }, $identity);

            return 1 === count($this->mapping->getIdentifierFieldNames(get_class($value))) ? reset($identity) : array_values($identity);
        }

        return $value;
    }

    /**
     * @param object $object
     */
    public function getIdentifiers($object): array
    {
        return array_values($this->mapping->getIdentity($object));
    }

    /**
     * @return string[]
     */
    public function getIdentifierFieldNames(string $class): array
    {
        return $this->mapping->getIdentifierFieldNames($class);
    }

    public function isIdentity(string $class, array $value): bool
    {
        if (!$value || count($value) !== count($fields = $this->mapping->getIdentifierFieldNames($class)) || in_array(null, $value, true)) {
            return false;
        }

        return [] === array_diff(array_keys($value), $fields);
    }

    public function toIdentity(string $class, $id, ...$idN): ?array
    {
        array_unshift($idN, $id);

        if (count($idN) !== count($fields = $this->mapping->getIdentifierFieldNames($class)) || in_array(null, $idN, true)) {
            return null;
        }

        return array_combine($fields, $idN);
    }

    /**
     * @param object $object
     */
    public function getIdentity($object): array
    {
        return $this->mapping->getIdentity($object);
    }
}
