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
    private $factory;
    private $identifierMapping;
    private $referenceLoaders;

    /**
     * @param callable[] $referenceLoaders
     */
    public function __construct(DomainObjectFactoryInterface $factory, array $identifierMapping, iterable $referenceLoaders = [])
    {
        $this->factory = $factory;
        $this->identifierMapping = $identifierMapping;
        $this->referenceLoaders = $referenceLoaders;
    }

    public function create(string $class, array $context = [])
    {
        return $this->factory->create($class, $context);
    }

    public function reference(string $class, $id, ...$idN)
    {
        array_unshift($idN, $id);

        foreach ($this->referenceLoaders as $loader) {
            if (is_object($object = $loader($class, $idN))) {
                return $object;
            }
        }

        throw new \RuntimeException(sprintf('Unable to create a reference object for "%s".', $class));
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
