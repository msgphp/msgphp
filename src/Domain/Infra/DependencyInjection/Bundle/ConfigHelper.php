<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Bundle;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Infra\Uuid\DomainId as DomainUuid;
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

    public static function createClassMappingNode(string $name, array $required = [], \Closure $normalizer = null, $defaultValue = null, NodeBuilder $builder = null): ArrayNodeDefinition
    {
        $node = ($builder ?? new NodeBuilder())->arrayNode($name);

        if ($required) {
            $node->isRequired();

            foreach ($required as $class) {
                $node->validate()
                    ->ifTrue(function (array $value) use ($class) {
                        return !isset($value[$class]);
                    })
                    ->thenInvalid(sprintf('Class "%s" must be configured.', $class))
                ->end();
            }
        }

        if ($normalizer) {
            $node->beforeNormalization()->always($normalizer);
        }

        $node
            ->useAttributeAsKey('class')
            ->scalarPrototype()->defaultValue($defaultValue)->end();

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

                $value = DomainUuid::class;
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

    private function __construct()
    {
    }
}
