<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Entity;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Exception\InvalidEntityClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @fixme split into / decorate Domain\ConstructorResolvingDomainObjectFactory
 */
final class ClassMappingEntityFactory implements EntityFactoryInterface
{
    private static $reflectionCache = [];

    private $mapping;
    private $idMapping;
    private $factory;

    public function __construct(array $mapping, array $idMapping, EntityFactoryInterface $factory = null)
    {
        $this->mapping = array_change_key_case(array_combine($values = array_values($mapping), $values) + $mapping, \CASE_LOWER);
        $this->idMapping = array_change_key_case($idMapping, \CASE_LOWER);
        $this->factory = $factory;
    }

    public function create(string $entity, array $context = [])
    {
        if (!isset($this->mapping[$lcEntity = ltrim(strtolower($entity), '\\')])) {
            throw InvalidEntityClassException::create($entity);
        }

        $class = $this->mapping[$lcEntity];

        return new $class(...$this->getConstructorArguments($class, $context));
    }

    public function identify(string $entity, $id): DomainIdInterface
    {
        if (!isset($this->idMapping[$lcEntity = ltrim(strtolower($entity), '\\')])) {
            throw InvalidEntityClassException::create($entity);
        }

        return $this->create($this->idMapping[$lcEntity], [$id]);
    }

    public function nextIdentity(string $entity): DomainIdInterface
    {
        if (!isset($this->idMapping[$lcEntity = ltrim(strtolower($entity), '\\')])) {
            throw InvalidEntityClassException::create($entity);
        }

        return $this->create($this->idMapping[$lcEntity]);
    }

    private function getConstructorArguments(string $class, array $context)
    {
        if (!isset(self::$reflectionCache[$lcClass = ltrim(strtolower($class), '\\')])) {
            if (null === ($constructor = (new \ReflectionClass($class))->getConstructor())) {
                return self::$reflectionCache[$lcClass] = [];
            }

            self::$reflectionCache[$lcClass] = array_map(function (\ReflectionParameter $param) {
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
                } catch (InvalidEntityClassException $e) {
                }
            }

            $arguments[] = $value;
        }

        return $arguments;
    }
}
