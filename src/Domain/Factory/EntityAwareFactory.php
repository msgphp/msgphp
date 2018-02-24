<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\{DomainIdentityMappingInterface, DomainIdInterface};
use MsgPhp\Domain\Exception\InvalidClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EntityAwareFactory implements EntityAwareFactoryInterface
{
    private $factory;
    private $identityMapping;
    private $identifierMapping;

    public function __construct(DomainObjectFactoryInterface $factory, DomainIdentityMappingInterface $identityMapping, array $identifierMapping = [])
    {
        $this->factory = $factory;
        $this->identityMapping = $identityMapping;
        $this->identifierMapping = $identifierMapping;
    }

    public function create(string $class, array $context = [])
    {
        return $this->factory->create($class, $context);
    }

    public function reference(string $class, $id)
    {
        $idFields = $this->identityMapping->getIdentifierFieldNames($class);

        if (!is_array($id)) {
            $id = [array_shift($idFields) => $id];
        }

        return $this->factory->create($class, $id + array_fill_keys($idFields, null));
    }

    public function identify(string $class, $value): DomainIdInterface
    {
        if ($value instanceof DomainIdInterface) {
            return $value;
        }

        $object = $this->factory->create($this->identifierMapping[$class] ?? $class, [$value]);

        if (!$object instanceof DomainIdInterface) {
            throw InvalidClassException::create($class);
        }

        return $object;
    }

    public function nextIdentifier(string $class): DomainIdInterface
    {
        $object = $this->factory->create($this->identifierMapping[$class] ?? $class);

        if (!$object instanceof DomainIdInterface) {
            throw InvalidClassException::create($class);
        }

        return $object;
    }
}
