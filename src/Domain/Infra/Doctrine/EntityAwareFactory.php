<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use Doctrine\Common\Persistence\Mapping\MappingException;
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

    public function __construct(EntityAwareFactoryInterface $factory, EntityManagerInterface $em)
    {
        $this->factory = $factory;
        $this->em = $em;
    }

    public function create(string $class, array $context = [])
    {
        return $this->factory->create($this->resolveDiscriminatorClass($class, $context), $context);
    }

    public function getClass(string $class, array $context = []): string
    {
        return $this->resolveDiscriminatorClass($class, $context);
    }

    public function reference(string $class, $id)
    {
        if (\is_array($id)) {
            $class = $this->resolveDiscriminatorClass($class, $id, true);
        } else {
            $class = $this->factory->getClass($class);
        }

        try {
            $ref = $this->em->getReference($class, $id);
        } catch (MappingException $e) {
            $ref = null;
        }

        if (null === $ref) {
            throw InvalidClassException::create($class);
        }

        return $ref;
    }

    public function identify(string $class, $value): DomainIdInterface
    {
        return $this->factory->identify($class, $value);
    }

    public function nextIdentifier(string $class): DomainIdInterface
    {
        return $this->factory->nextIdentifier($class);
    }

    private function resolveDiscriminatorClass(string $class, array &$context, bool $clear = false): string
    {
        $class = $this->factory->getClass($class, $context);

        if ($this->em->getMetadataFactory()->isTransient($class)) {
            return $class;
        }

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
