<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\Exception\InvalidClassException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ClassMethodResolver
{
    private static $cache = [];

    public static function resolve(string $class, string $method): array
    {
        if (isset(self::$cache[$key = $class.'::'.$method])) {
            return self::$cache[$key];
        }

        try {
            $reflection = new \ReflectionClass($class);
            $reflection = '__construct' === $method ? $reflection->getConstructor() : $reflection->getMethod($method);
        } catch (\ReflectionException $e) {
            throw InvalidClassException::createForMethod($class, $method);
        }

        if (null === $reflection) {
            return self::$cache[$key] = [];
        }

        return self::$cache[$key] = array_map(function (\ReflectionParameter $param): array {
            $type = $param->getType();

            if (null !== $type) {
                if ('self' === strtolower($name = $type->getName())) {
                    $type = $param->getClass()->getName();
                } elseif ($type->isBuiltin()) {
                    $type = $name;
                } else {
                    try {
                        $type = (new \ReflectionClass($name))->getName();
                    } catch (\ReflectionException $e) {
                        $type = $name;
                    }
                }
            }

            return [
                'name' => $param->getName(),
                'required' => !$param->isDefaultValueAvailable() && !$param->allowsNull(),
                'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                'type' => $type,
            ];
        }, $reflection->getParameters());
    }
}
