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

            return [
                'name' => $name = $param->getName(),
                'key' => strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), $name)),
                'required' => $required,
                'default' => $default,
                'type' => $type,
            ];
        }, $reflection->getParameters());
    }
}
