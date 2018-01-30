<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\{DomainIdInterface, DomainCollectionInterface};
use MsgPhp\Domain\Exception\InvalidClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainObjectFactory implements DomainObjectFactoryInterface
{
    private $factory;

    public function setNestedFactory(?DomainObjectFactoryInterface $factory): void
    {
        $this->factory = $factory;
    }

    public function create(string $class, array $context = [])
    {
        if (is_subclass_of($class, DomainIdInterface::class) || is_subclass_of($class, DomainCollectionInterface::class)) {
            return $class::fromValue(...$this->resolveArguments($class, 'fromValue', $context));
        }

        if (!class_exists($class)) {
            throw InvalidClassException::create($class);
        }

        return new $class(...$this->resolveArguments($class, '__construct', $context));
    }

    private function resolveArguments(string $class, string $method, array $context): array
    {
        $arguments = [];

        foreach (ClassMethodResolver::resolve($class, $method) as $i => $argument) {
            $given = true;
            if (array_key_exists($name = $argument['name'], $context)) {
                $value = $context[$name];
            } elseif (array_key_exists($key = $argument['key'], $context)) {
                $value = $context[$key];
            } elseif (array_key_exists($i, $context)) {
                $value = $context[$i];
            } elseif (!$argument['required']) {
                $value = $argument['default'];
                $given = false;
            } else {
                throw new \LogicException(sprintf('No value available for argument $%s in class method "%s::%s()".', $name, $class, $method));
            }

            if ($given && isset($argument['type']) && (interface_exists($argument['type']) || class_exists($argument['type'])) && !is_object($value)) {
                try {
                    $arguments[] = ($this->factory ?? $this)->create($argument['type'], (array) $value);

                    continue;
                } catch (InvalidClassException $e) {
                }
            }

            $arguments[] = $value;
        }

        return $arguments;
    }
}
