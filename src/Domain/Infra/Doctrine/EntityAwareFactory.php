<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EntityAwareFactory implements EntityAwareFactoryInterface
{
    private $factory;
    private $em;
    private $classMapping;

    public function __construct(EntityAwareFactoryInterface $factory, EntityManagerInterface $em, array $classMapping = [])
    {
        $this->factory = $factory;
        $this->em = $em;
        $this->classMapping = $classMapping;
    }

    public function create(string $class, array $context = [])
    {
        return $this->factory->create($this->classMapping[$class] ?? $class, $context);
    }

    public function reference(string $class, $id)
    {
        $class = $this->classMapping[$class] ?? $class;

        if (!class_exists($class) || $this->em->getMetadataFactory()->isTransient($class) || null === $ref = $this->em->getReference($class, $id)) {
            throw InvalidClassException::create($class);
        }

        return $ref;
    }

    public function identify(string $class, $value): DomainIdInterface
    {
        return $this->factory->identify($this->classMapping[$class] ?? $class, $value);
    }

    public function nextIdentifier(string $class): DomainIdInterface
    {
        return $this->factory->nextIdentifier($this->classMapping[$class] ?? $class);
    }
}
