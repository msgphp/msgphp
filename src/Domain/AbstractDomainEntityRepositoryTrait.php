<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

use MsgPhp\Domain\Exception\InvalidClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait AbstractDomainEntityRepositoryTrait
{
    private $class;
    private $identityMapping;
    private $fieldMapping;

    public function __construct(string $class, DomainIdentityMappingInterface $identityMapping, array $fieldMapping = [])
    {
        $this->class = $class;
        $this->identityMapping = $identityMapping;
        $this->fieldMapping = $fieldMapping;
    }

    private function isEmptyIdentifier($value): bool
    {
        if ($value instanceof DomainIdInterface && $value->isEmpty()) {
            return true;
        }

        if (is_object($value)) {
            try {
                if (!$this->identityMapping->getIdentity($value)) {
                    return true;
                }
            } catch (InvalidClassException $e) {
            }
        }

        return false;
    }

    private function normalizeIdentifier($value)
    {
        if ($value instanceof DomainIdInterface) {
            return $value->isEmpty() ? null : $value->toString();
        }

        if (is_object($value)) {
            try {
                $identity = $this->identityMapping->getIdentity($value);
            } catch (InvalidClassException $e) {
                return null;
            };

            $identity = array_map(function ($id) {
                return $this->normalizeIdentifier($id);
            }, $identity);

            return 1 === count($identity) ? reset($identity) : $identity;
        }

        return $value;
    }

    private function toIdentity($id, ...$idN): ?array
    {
        if (count($ids = func_get_args()) !== count($this->identityMapping->getIdentifierFieldNames($this->class))) {
            return null;
        }

        foreach ($ids as $id) {
            if ($this->isEmptyIdentifier($id)) {
                return null;
            }
        }

        return array_combine($this->identityMapping->getIdentifierFieldNames($this->class), $ids);
    }

    private function isIdentity(array $fields): bool
    {
        if (count($fields) !== count($idFields = $this->identityMapping->getIdentifierFieldNames($this->class))) {
            return false;
        }

        $fields = array_map(function (string $field) {
            return $this->fieldMapping[$field] ?? $field;
        }, array_keys($fields));

        return [] === array_diff($fields, $idFields);
    }

    private function mapFields(array $fields): iterable
    {
        foreach ($fields as $key => $value) {
            yield $this->fieldMapping[$key] ?? $key => $value;
        }
    }
}
