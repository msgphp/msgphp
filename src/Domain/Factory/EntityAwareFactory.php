<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Exception\InvalidClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EntityAwareFactory implements EntityAwareFactoryInterface
{
    private $identifierMapping;
    private $factory;

    public function __construct(array $identifierMapping, DomainObjectFactoryInterface $factory)
    {
        $this->identifierMapping = $identifierMapping;
        $this->factory = $factory;
    }

    public function create(string $class, array $context = [])
    {
        return $this->factory->create($class, $context);
    }

    public function identify(string $class, $id): DomainIdInterface
    {
        if ($id instanceof DomainIdInterface) {
            return $id;
        }

        $object = $this->factory->create($this->identifierMapping[$class] ?? $class, [$id]);

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
