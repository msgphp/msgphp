<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\Exception\InvalidClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChainObjectFactory implements DomainObjectFactoryInterface
{
    private $factories;

    /**
     * @param DomainObjectFactoryInterface[] $factories
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
            } catch (InvalidClassException $e) {
            }
        }

        throw InvalidClassException::create($entity);
    }
}
