<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\Exception\InvalidClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConstructorResolvingObjectFactory implements DomainObjectFactoryInterface
{
    private static $reflectionCache = [];

    private $factory;

    public function setNestedFactory(?DomainObjectFactoryInterface $factory): void
    {
        $this->factory = $factory;
    }

    public function create(string $class, array $context = [])
    {
        if (!class_exists($class)) {
            throw InvalidClassException::create($class);
        }

        return new $class(...$this->resolveConstructorArguments($class, $context));
    }

    private function resolveConstructorArguments(string $class, array $context): array
    {
        if (!isset(self::$reflectionCache[$lcClass = ltrim(strtolower($class), '\\')])) {
            if (null === ($constructor = (new \ReflectionClass($class))->getConstructor())) {
                return self::$reflectionCache[$lcClass] = [];
            }

            self::$reflectionCache[$lcClass] = array_map(function (\ReflectionParameter $param): array {
                return [
                    strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), $param->getName())),
                    $param->isDefaultValueAvailable() || $param->allowsNull(),
                    $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                    null !== ($type = $param->getType()) && !$type->isBuiltin()
                        ? ('self' === strtolower($name = $type->getName())
                            ? $param->getClass()->getName()
                            : $name)
                        : null,
                ];
            }, $constructor->getParameters());
        }

        $arguments = [];

        foreach (self::$reflectionCache[$lcClass] as $i => $argument) {
            list($key, $hasDefault, $default, $type) = $argument;

            if (array_key_exists($key, $context)) {
                $value = $context[$key];
                $hasContext = true;
            } elseif (array_key_exists($i, $context)) {
                $value = $context[$i];
                $hasContext = true;
            } else {
                $value = $default;
                $hasContext = false;
            }

            if (!$hasContext && !$hasDefault) {
                throw new \LogicException(sprintf('No value available for constructor argument #%d in class "%s".', $i, $class));
            }

            if (null !== $type && $hasContext && !is_object($value)) {
                try {
                    $arguments[] = ($this->factory ?? $this)->create($type, (array) $value);

                    continue;
                } catch (InvalidClassException $e) {
                }
            }

            $arguments[] = $value;
        }

        return $arguments;
    }
}
