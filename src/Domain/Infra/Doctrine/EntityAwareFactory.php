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
        if (!$this->isManaged($class = $this->classMapping[$class] ?? $class)) {
            throw InvalidClassException::create($class);
        }

        return $this->factory->create($this->getDiscriminatorClass($class, $context), $context);
    }

    public function reference(string $class, $id)
    {
        if (!$this->isManaged($class = $this->classMapping[$class] ?? $class)) {
            throw InvalidClassException::create($class);
        }
        if (is_array($id)) {
            $class = $this->getDiscriminatorClass($class, $id, true);
        }
        if (null === $ref = $this->em->getReference($class, $id)) {
            throw InvalidClassException::create($class);
        }

        return $ref;
    }

    public function identify(string $class, $value): DomainIdInterface
    {
        if (!$this->isManaged($class = $this->classMapping[$class] ?? $class)) {
            throw InvalidClassException::create($class);
        }

        return $this->factory->identify($class, $value);
    }

    public function nextIdentifier(string $class): DomainIdInterface
    {
        if (!$this->isManaged($class = $this->classMapping[$class] ?? $class)) {
            throw InvalidClassException::create($class);
        }

        return $this->factory->nextIdentifier($class);
    }

    private function isManaged(string $class): bool
    {
        return class_exists($class) && !$this->em->getMetadataFactory()->isTransient($class);
    }

    private function getDiscriminatorClass(string $class, array &$context, bool $clear = false): string
    {
        $metadata = $this->em->getClassMetadata($class);

        if (isset($metadata->discriminatorColumn['fieldName'], $context[$metadata->discriminatorColumn['fieldName']])) {
            $class = $metadata->discriminatorMap[$context[$metadata->discriminatorColumn['fieldName']]] ?? $class;

            if ($clear) {
                unset($context[$metadata->discriminatorColumn['fieldName']]);
            }
        }

        unset($context);

        return $class;
    }
}
