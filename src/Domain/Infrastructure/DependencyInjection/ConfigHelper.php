<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\DependencyInjection;

use MsgPhp\Domain\Event\DomainEventHandler;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ConfigHelper
{
    public const DEFAULT_ID_TYPE = 'integer';

    public static function resolveCommandMappingConfig(array $commandMapping, array $classMapping, array &$config): void
    {
        foreach ($commandMapping as $class => $features) {
            $available = isset($classMapping[$class]);
            $handlerAvailable = $available && is_subclass_of($classMapping[$class], DomainEventHandler::class);

            foreach ($features as $feature => $info) {
                if (\is_array($info)) {
                    $config += array_fill_keys($info, $available && self::uses($classMapping[$class], $feature) ? $handlerAvailable : false);
                } else {
                    $config += [$info => $available];
                }
            }
        }
    }

    private static function uses(string $class, string $trait): bool
    {
        static $uses = [];

        if (!isset($uses[$class])) {
            $resolve = static function (string $class) use (&$resolve): array {
                $resolved = [];

                foreach (class_uses($class) as $used) {
                    $resolved[$used] = true;
                    $resolved += $resolve($used);
                }

                return $resolved;
            };

            $uses[$class] = $resolve($class);
        }

        return isset($uses[$class][$trait]);
    }
}
