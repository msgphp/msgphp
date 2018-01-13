<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\InMemory;

use MsgPhp\Domain\DomainIdentityMapInterface;
use MsgPhp\Domain\Exception\InvalidClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainIdentityMap implements DomainIdentityMapInterface
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

        return (array) $this->mapping[$class];
    }

    public function getIdentity($entity): array
    {
        $ids = [];

        foreach ($this->getIdentifierFieldNames(get_class($entity)) as $field) {
            $ids[$field] = $this->accessor->getValue($entity, $field);
        }

        return $ids;
    }
}
