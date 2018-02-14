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
                if (null === $this->mapping->getIdentity($value)) {
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
                if (null === $identity = $this->mapping->getIdentity($value)) {
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
        return null === ($identity = $this->mapping->getIdentity($object)) ? [] : array_values($identity);
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

    public function toIdentity(string $class, $value): ?array
    {
        if (null === $value || 0 === $count = count($fields = $this->mapping->getIdentifierFieldNames($class))) {
            return null;
        }

        if (!is_array($value)) {
            return 1 === $count ? [reset($fields) => $value] : null;
        }

        if ([] !== array_diff(array_keys($value), $fields) || in_array(null, $value, true)) {
            return null;
        }

        return $value;
    }

    /**
     * @param object $object
     */
    public function getIdentity($object): ?array
    {
        return $this->mapping->getIdentity($object);
    }
}
