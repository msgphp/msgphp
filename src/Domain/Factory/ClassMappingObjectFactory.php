<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ClassMappingObjectFactory implements DomainObjectFactoryInterface
{
    private $mapping;
    private $factory;

    public function __construct(array $mapping, DomainObjectFactoryInterface $factory)
    {
        $this->mapping = $mapping;
        $this->factory = $factory;
    }

    public function create(string $class, array $context = [])
    {
        return $this->factory->create($this->mapping[$class] ?? $class, $context);
    }
}
