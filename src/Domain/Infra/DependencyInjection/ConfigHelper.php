<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Event\DomainEventHandlerInterface;
use MsgPhp\Domain\Infra\Uuid as UuidInfra;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ConfigHelper
{
    public const DEFAULT_ID_TYPE = 'integer';
    public const UUID_TYPES = ['uuid', 'uuid_binary', 'uuid_binary_ordered_time'];

    public static function defaultBundleConfig(array $defaultIdClassMapping, array $idClassMappingPerType): \Closure
    {
        return function (array $value) use ($defaultIdClassMapping, $idClassMappingPerType): array {
            $defaultType = $value['default_id_type'] ?? ConfigHelper::DEFAULT_ID_TYPE;
            unset($value['default_id_type']);

            if (isset($value['id_type_mapping'])) {
                foreach ($value['id_type_mapping'] as $class => $type) {
                    if (isset($value['class_mapping'][$class])) {
                        continue;
                    }

                    if (null === $mappedClass = $idClassMapping[$type][$class] ?? $defaultIdClassMapping[$class] ?? null) {
                        $mappedClass = in_array($type, self::UUID_TYPES, true) ? UuidInfra\DomainId::class : DomainId::class;
                    }

                    $value['class_mapping'][$class] = $mappedClass;
                }
            }

            if (isset($idClassMappingPerType[$defaultType])) {
                $value['class_mapping'] += $idClassMappingPerType[$defaultType];
                $value['id_type_mapping'] += array_fill_keys(array_keys($idClassMappingPerType[$defaultType]), $defaultType);
            }

            $value['class_mapping'] += $defaultIdClassMapping;
            $value['id_type_mapping'] += array_fill_keys(array_keys($defaultIdClassMapping), $defaultType);

            return $value;
        };
    }

    public static function resolveCommandMappingConfig(array $commandMapping, array $classMapping, array &$config): void
    {
        foreach ($commandMapping as $commandClass => $features) {
            $mappedClass = $classMapping[$commandClass] ?? $commandClass;
            $isEventHandler = is_subclass_of($mappedClass, DomainEventHandlerInterface::class);

            foreach ($features as $feature => $featureInfo) {
                if (!trait_exists($feature)) {
                    $config[$feature] = $featureInfo;
                } elseif (self::uses($mappedClass, $feature)) {
                    $config += array_fill_keys($featureInfo, $isEventHandler);
                }
            }
        }
    }

    private static function uses(string $class, string $trait): bool
    {
        static $uses = [];

        if (!isset($uses[$class])) {
            $resolve = function (string $class) use (&$resolve): array {
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

    private function __construct()
    {
    }
}
