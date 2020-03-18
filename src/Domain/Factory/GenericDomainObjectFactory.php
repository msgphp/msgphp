<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\Exception\InvalidClass;
use MsgPhp\Domain\Infrastructure\Uid\DomainUuid;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;
use Symfony\Component\VarExporter\Instantiator;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class GenericDomainObjectFactory implements DomainObjectFactory
{
    /** @var array<class-string, class-string> $classMapping */
    private $classMapping;
    /** @var null|DomainObjectFactory */
    private $factory;

    /**
     * @param array<class-string, class-string> $classMapping
     */
    public function __construct(array $classMapping = [])
    {
        $this->classMapping = $classMapping;
    }

    public function setNestedFactory(?DomainObjectFactory $factory): void
    {
        $this->factory = $factory;
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function create(string $class, array $context = []): object
    {
        $class = $this->getClass($class, $context);

        if (is_a($class, DomainUuid::class, true) && \array_key_exists('value', $context)) {
            /** @var T */
            return $class::fromString(...$this->resolveArguments($class, 'fromString', $context));
        }

        if (!class_exists($class)) {
            throw InvalidClass::create($class);
        }

        /** @var T */
        return new $class(...$this->resolveArguments($class, '__construct', $context));
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function reference(string $class, array $context = []): object
    {
        if (!class_exists(Instantiator::class)) {
            throw new \LogicException('Method "'.__METHOD__.'()" requires "symfony/var-exporter".');
        }

        $class = $this->getClass($class, $context);
        $properties = [];
        foreach ($context as $key => $value) {
            if (property_exists($class, $key)) {
                $properties[$key] = $value;

                continue;
            }
        }

        try {
            /** @var T */
            return Instantiator::instantiate($class, $properties);
        } catch (ClassNotFoundException $e) {
            throw InvalidClass::create($class);
        }
    }

    public function getClass(string $class, array $context = []): string
    {
        return $this->classMapping[$class] ?? $class;
    }

    /**
     * @param class-string $class
     *
     * @return array<int, mixed>
     */
    private function resolveArguments(string $class, string $method, array $context): array
    {
        $arguments = [];

        foreach (ClassMethodResolver::resolve($class, $method) as $argument => $metadata) {
            if (\array_key_exists($argument, $context)) {
                $given = true;
                $value = $context[$argument];
            } elseif (!$metadata['required']) {
                $given = false;
                $value = $metadata['default'];
            } else {
                throw new \LogicException('No value available for argument $'.$argument.' in class method "'.$class.'::'.$method.'()".');
            }

            $type = $metadata['type'];
            if ($given && !\is_object($value) && (class_exists($type) || interface_exists($type, false))) {
                $arguments[] = ($this->factory ?? $this)->create($type, (array) $value);

                continue;
            }

            $arguments[] = $value;
        }

        return $arguments;
    }
}
