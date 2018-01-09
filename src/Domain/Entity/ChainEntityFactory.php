<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Entity;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Exception\InvalidEntityClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @fixme rename to Domain\ChainDomainObjectFactory
 */
final class ChainEntityFactory implements EntityFactoryInterface
{
    private $factories;

    /**
     * @param EntityFactoryInterface[] $factories
     */
    public function __construct(iterable $factories)
    {
        $this->factories = $factories;
    }

    public function create(string $entity, array $context = [])
    {
        foreach ($this->factories as $factory) {
            try {
                return $factory->create($entity, $context);
            } catch (InvalidEntityClassException $e) {
            }
        }

        throw InvalidEntityClassException::create($entity);
    }

    public function identify(string $entity, $id): DomainIdInterface
    {
        foreach ($this->factories as $factory) {
            try {
                return $factory->identify($entity, $id);
            } catch (InvalidEntityClassException $e) {
            }
        }

        throw InvalidEntityClassException::create($entity);
    }

    public function nextIdentity(string $entity): DomainIdInterface
    {
        foreach ($this->factories as $factory) {
            try {
                return $factory->nextIdentity($entity);
            } catch (InvalidEntityClassException $e) {
            }
        }

        throw InvalidEntityClassException::create($entity);
    }
}
