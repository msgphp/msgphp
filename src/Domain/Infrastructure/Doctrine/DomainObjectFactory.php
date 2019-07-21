<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Domain\Exception\InvalidClass;
use MsgPhp\Domain\Factory\DomainObjectFactory as BaseDomainObjectFactory;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainObjectFactory implements BaseDomainObjectFactory
{
    private $factory;
    private $em;

    public function __construct(BaseDomainObjectFactory $factory, EntityManagerInterface $em)
    {
        $this->factory = $factory;
        $this->em = $em;
    }

    public function create(string $class, array $context = []): object
    {
        /** @var T */
        return $this->factory->create($this->resolveDiscriminatorClass($class, $context), $context);
    }

    public function reference(string $class, array $context = []): object
    {
        $class = $this->factory->getClass($class, $context);

        if ($this->em->getMetadataFactory()->isTransient($class)) {
            /** @var T */
            return $this->factory->reference($class, $context);
        }

        if (null === $ref = $this->em->getReference($class, $context)) {
            throw InvalidClass::create($class);
        }

        /** @var T */
        return $ref;
    }

    public function getClass(string $class, array $context = []): string
    {
        return $this->resolveDiscriminatorClass($class, $context);
    }

    /**
     * @param class-string $class
     *
     * @return class-string
     */
    private function resolveDiscriminatorClass(string $class, array $context): string
    {
        $class = $this->factory->getClass($class, $context);

        if ($this->em->getMetadataFactory()->isTransient($class)) {
            return $class;
        }

        $metadata = $this->em->getClassMetadata($class);

        if (isset($metadata->discriminatorColumn['fieldName'], $context[$metadata->discriminatorColumn['fieldName']])) {
            $class = $metadata->discriminatorMap[$context[$metadata->discriminatorColumn['fieldName']]] ?? $class;
        }

        return $class;
    }
}
