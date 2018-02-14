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
    private $referenceLoader;

    public function __construct(DomainObjectFactoryInterface $factory, array $identifierMapping, callable $referenceLoader = null)
    {
        $this->factory = $factory;
        $this->identifierMapping = $identifierMapping;
        $this->referenceLoader = $referenceLoader;
    }

    public function create(string $class, array $context = [])
    {
        return $this->factory->create($class, $context);
    }

    public function reference(string $class, $id)
    {
        if (null === $this->referenceLoader) {
            throw new \LogicException('No reference loader set.');
        }

        if (is_object($object = ($this->referenceLoader)($class, $id))) {
            return $object;
        }

        throw new \RuntimeException(sprintf('Unable to create a reference object for "%s".', $class));
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
