<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\Exception\InvalidClass;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ClassMethodResolver
{
    /** @var array<string, array> */
    private static $cache = [];

    /**
     * @param class-string $class
     *
     * @return array<string, array{index:int,required:bool,default:mixed,type:string|class-string}>
     */
    public static function resolve(string $class, string $method): array
    {
        if (isset(self::$cache[$key = $class.'::'.$method])) {
            return self::$cache[$key];
        }

        try {
            $reflection = new \ReflectionClass($class);
            $reflection = '__construct' === $method ? $reflection->getConstructor() : $reflection->getMethod($method);
        } catch (\ReflectionException $e) {
            throw InvalidClass::createForMethod($class, $method);
        }

        if (null === $reflection || !$reflection->getNumberOfParameters()) {
            return self::$cache[$key] = [];
        }

        foreach ($reflection->getParameters() as $i => $param) {
            if (null === $type = $param->getType()) {
                $type = 'mixed';
            } elseif ('self' === strtolower($name = $type->getName())) {
                /** @psalm-suppress PossiblyNullReference */
                $type = $param->getClass()->getName();
            } elseif ($type->isBuiltin()) {
                /** @psalm-suppress UndefinedVariable */
                $type = $name;
            } else {
                try {
                    /** @psalm-suppress TypeCoercion */
                    $type = (new \ReflectionClass($name))->getName();
                } catch (\ReflectionException $e) {
                    $type = $name;
                }
            }

            $required = false;
            if ($param->isDefaultValueAvailable()) {
                $default = $param->getDefaultValue();
            } elseif ($param->allowsNull()) {
                $default = null;
            } elseif ('array' === $type || 'iterable' === $type) {
                $default = [];
                $required = true;
            } else {
                $default = null;
                $required = true;
            }

            self::$cache[$key][$param->getName()] = [
                'index' => $i,
                'required' => $required,
                'default' => $default,
                'type' => $type,
            ];
        }

        return self::$cache[$key];
    }
}
