<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Bundle;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Event\DomainEventHandlerInterface;
use MsgPhp\Domain\Infra\Uuid as UuidInfra;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ConfigHelper
{
    public const NATIVE_DATA_TYPES = ['string', 'integer', 'bigint'];
    public const UUID_DATA_TYPES = ['uuid', 'uuid_binary', 'uuid_binary_ordered_time'];

    public static function createClassMappingNode(string $name, array $required = [], \Closure $normalizer = null, $defaultValue = null, string $prototype = 'scalar', \Closure $prototypeCallback = null, NodeBuilder $builder = null): ArrayNodeDefinition
    {
        $node = ($builder ?? new NodeBuilder())->arrayNode($name);
        $node->useAttributeAsKey('class');

        if ($required) {
            $node->isRequired();

            foreach ($required as $class) {
                $node->validate()->ifTrue(function (array $value) use ($class) {
                    return !isset($value[$class]);
                })->thenInvalid(sprintf('Class "%s" must be configured.', $class));
            }
        }

        if (null !== $normalizer) {
            $node->beforeNormalization()->always($normalizer);
        }

        $prototype = $node->prototype($prototype);
        $prototype->defaultValue($defaultValue);

        if (null !== $prototypeCallback) {
            $prototypeCallback($prototype);
        }

        return $node;
    }

    public static function resolveResolveDataTypeMapping(ContainerBuilder $container, array &$config): void
    {
        if (!$container->hasParameter('msgphp.default_data_type')) {
            $container->setParameter('msgphp.default_data_type', 'integer');
        }

        foreach ($config as &$value) {
            $value = $container->getParameterBag()->resolveValue($value ?? '%msgphp.default_data_type%');
        }

        unset($value, $config);
    }

    public static function resolveClassMapping(array $dataTypeMap, array $dataTypeMapping, array &$config): void
    {
        foreach ($config as $key => &$value) {
            if (null !== $value) {
                continue;
            }

            if (!isset($dataTypeMapping[$key])) {
                $value = $key;
                continue;
            }

            $value = DomainId::class;
            if (in_array($dataType = $dataTypeMapping[$key], self::UUID_DATA_TYPES, true)) {
                if (!interface_exists(UuidInterface::class)) {
                    throw new \LogicException(sprintf('Data type "%s" for identifier "%s" requires "ramsey/uuid".', $dataType, $key));
                }

                $value = UuidInfra\DomainId::class;
            }

            if (!isset($dataTypeMap[$key])) {
                continue;
            }

            foreach ($dataTypeMap[$key] as $class => $dataTypes) {
                if (in_array($dataType, $dataTypes, true)) {
                    $value = $class;
                    break;
                }
            }
        }

        unset($value, $config);
    }

    public static function resolveCommandMapping(array $classMapping, array $mapping, array &$config): void
    {
        foreach ($mapping as $class => $traits) {
            $mappedClass = $classMapping[$class] ?? $class;
            if ($class !== $mappedClass && !is_subclass_of($mappedClass, $class)) {
                continue;
            }

            $isEventHandler = is_subclass_of($mappedClass, DomainEventHandlerInterface::class);
            foreach ($traits as $trait => $traitConfig) {
                if (!self::uses($mappedClass, $trait)) {
                    continue;
                }

                $config += array_fill_keys($traitConfig, $isEventHandler);
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
