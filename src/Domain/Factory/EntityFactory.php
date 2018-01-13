<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Exception\InvalidClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EntityFactory implements EntityFactoryInterface
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
        $object = $this->factory->create($this->identifierMapping[$class] ?? $class, [$id]);

        if (!$object instanceof DomainIdInterface) {
            throw InvalidClassException::create($class);
        }

        return $object;
    }

    public function nextIdentity(string $class): DomainIdInterface
    {
        $object = $this->factory->create($this->identifierMapping[$class] ?? $class);

        if (!$object instanceof DomainIdInterface) {
            throw InvalidClassException::create($class);
        }

        return $object;
    }
}
