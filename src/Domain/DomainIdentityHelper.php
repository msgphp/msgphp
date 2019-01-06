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

        if (\is_object($value)) {
            try {
                $this->mapping->getIdentifierFieldNames(\get_class($value));

                return true;
            } catch (InvalidClassException $e) {
            }
        }

        return false;
    }

    public function isEmptyIdentifier($value): bool
    {
        if (null === $value) {
            return true;
        }

        if ($value instanceof DomainIdInterface) {
            return $value->isEmpty();
        }

        if (\is_object($value)) {
            try {
                $identifiers = $this->mapping->getIdentifiers($value);
            } catch (InvalidClassException $e) {
                return false;
            }

            return !$this->isIdentity(\get_class($value), $identifiers);
        }

        return false;
    }

    public function normalizeIdentifier($value)
    {
        if ($value instanceof DomainIdInterface) {
            return $value->isEmpty() ? null : $value->toString();
        }

        if (\is_object($value)) {
            try {
                $identifiers = $this->mapping->getIdentifiers($value);
            } catch (InvalidClassException $e) {
                return $value;
            }

            if (!$identifiers) {
                return null;
            }

            $identifiers = array_map(function ($id) {
                return $this->normalizeIdentifier($id);
            }, $identifiers);

            return 1 === \count($this->mapping->getIdentifierFieldNames(\get_class($value))) ? reset($identifiers) : $identifiers;
        }

        return $value;
    }

    /**
     * @return string[]
     */
    public function getIdentifierFieldNames(string $class): array
    {
        return $this->mapping->getIdentifierFieldNames($class);
    }

    /**
     * @param object $object
     */
    public function getIdentifiers($object): array
    {
        return $this->mapping->getIdentifiers($object);
    }

    public function isIdentity(string $class, $value): bool
    {
        if (null === $value || [] === $value) {
            return false;
        }

        $fields = $this->mapping->getIdentifierFieldNames($class);
        $count = \count($fields);

        if (\is_array($value)) {
            if (\count($value) !== $count) {
                return false;
            }

            if (($oldValue = $value) !== $value = array_filter($value, function ($value): bool {
                return !$this->isEmptyIdentifier($value);
            })) {
                return false;
            }

            return !array_diff_key($value, array_flip($fields));
        }

        return 1 === $count && !$this->isEmptyIdentifier($value);
    }

    public function toIdentity(string $class, $value): array
    {
        if (\is_array($value)) {
            return $value;
        }

        $fields = $this->mapping->getIdentifierFieldNames($class);

        return [reset($fields) => $value];
    }
}
