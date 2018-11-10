<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\{DomainIdentityHelper, DomainIdInterface};
use MsgPhp\Domain\Exception\InvalidClassException;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;
use Symfony\Component\VarExporter\Instantiator;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EntityAwareFactory implements EntityAwareFactoryInterface
{
    private $factory;
    private $identityHelper;
    private $identifierMapping;

    public function __construct(DomainObjectFactoryInterface $factory, DomainIdentityHelper $identityHelper, array $identifierMapping = [])
    {
        $this->factory = $factory;
        $this->identityHelper = $identityHelper;
        $this->identifierMapping = $identifierMapping;
    }

    public function create(string $class, array $context = [])
    {
        return $this->factory->create($class, $context);
    }

    public function getClass(string $class, array $context = []): string
    {
        return $this->factory->getClass($class, $context);
    }

    public function reference(string $class, $id)
    {
        if (!class_exists(Instantiator::class)) {
            throw new \LogicException(sprintf('Method "%s()" requires "symfony/var-exporter".', __METHOD__));
        }

        $class = $this->factory->getClass($class);

        if (!$this->identityHelper->isIdentity($class, $id)) {
            throw new \LogicException(sprintf('Invalid identity %s for class "%s".', (string) json_encode($id), $class));
        }

        $properties = [];
        foreach ($this->identityHelper->toIdentity($class, $id) as $field => $value) {
            if (property_exists($class, $field)) {
                $properties[$field] = $value;
                continue;
            }

            $properties[lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $field))))] = $value;
        }

        try {
            return Instantiator::instantiate($class, $properties);
        } catch (ClassNotFoundException $e) {
            throw InvalidClassException::create($class);
        }
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
