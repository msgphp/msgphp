<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainObjectFactory implements DomainObjectFactoryInterface
{
    /**
     * @var DomainObjectFactoryInterface
     */
    private $factory;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(DomainObjectFactoryInterface $factory, EntityManagerInterface $em)
    {
        $this->factory = $factory;
        $this->em = $em;
    }

    /**
     * @inheritdoc
     */
    public function create(string $class, array $context = [])
    {
        return $this->factory->create($this->resolveDiscriminatorClass($class, $context), $context);
    }

    /**
     * @inheritdoc
     */
    public function reference(string $class, array $context = [])
    {
        $class = $this->factory->getClass($class, $context);

        if ($this->em->getMetadataFactory()->isTransient($class)) {
            return $this->factory->reference($class, $context);
        }

        if (null === $ref = $this->em->getReference($class, $context)) {
            throw InvalidClassException::create($class);
        }

        return $ref;
    }

    /**
     * @inheritdoc
     */
    public function getClass(string $class, array $context = []): string
    {
        return $this->resolveDiscriminatorClass($class, $context);
    }

    /**
     * @psalm-param class-string $class
     * @psalm-return class-string
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
