<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\InMemory;

use MsgPhp\Domain\{DomainIdentityMappingInterface, DomainIdInterface};
use MsgPhp\Domain\Exception\InvalidClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainIdentityMapping implements DomainIdentityMappingInterface
{
    private $mapping;
    private $accessor;

    public function __construct(array $mapping, ObjectFieldAccessor $accessor = null)
    {
        $this->mapping = $mapping;
        $this->accessor = $accessor ?? new ObjectFieldAccessor();
    }

    public function getIdentifierFieldNames(string $class): array
    {
        if (!isset($this->mapping[$class])) {
            throw InvalidClassException::create($class);
        }

        if (!$fields = (array) $this->mapping[$class]) {
            throw new \LogicException(sprintf('No identifier fields available for class "%s".', $class));
        }

        return $fields;
    }

    public function getIdentity($object): array
    {
        $ids = [];

        foreach ($this->getIdentifierFieldNames(get_class($object)) as $field) {
            if (null === ($value = $this->accessor->getValue($object, $field))) {
                continue;
            }

            if ($value instanceof DomainIdInterface) {
                if ($value->isEmpty()) {
                    continue;
                }
            } elseif (is_object($value) && !$this->getIdentity($value)) {
                continue;
            }

            $ids[$field] = $value;
        }

        return $ids;
    }
}
