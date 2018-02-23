<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Domain\DomainIdInterface;
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
        return $this->factory->create($class, $context);
    }

    public function reference(string $class, $id)
    {
        return $this->em->getReference($class, $id);
    }

    public function identify(string $class, $value): DomainIdInterface
    {
        return $this->factory->identify($class, $value);
    }

    public function nextIdentifier(string $class): DomainIdInterface
    {
        return $this->factory->nextIdentifier($class);
    }
}
