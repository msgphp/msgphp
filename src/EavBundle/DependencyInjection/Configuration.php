<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle\DependencyInjection;

use MsgPhp\Domain\Infra\DependencyInjection\ConfigHelper;
use MsgPhp\Eav\{AttributeId, AttributeIdInterface, AttributeValueId, AttributeValueIdInterface, Entity};
use MsgPhp\Eav\Infra\Uuid as UuidInfra;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Configuration implements ConfigurationInterface
{
    public const REQUIRED_AGGREGATE_ROOTS = [
        Entity\Attribute::class => AttributeIdInterface::class,
        Entity\AttributeValue::class => AttributeValueIdInterface::class,
    ];
    public const OPTIONAL_AGGREGATE_ROOTS = [];
    public const AGGREGATE_ROOTS = self::REQUIRED_AGGREGATE_ROOTS + self::OPTIONAL_AGGREGATE_ROOTS;
    public const IDENTITY_MAPPING = [
        Entity\Attribute::class => 'id',
        Entity\AttributeValue::class => 'id',
    ];
    public const DATA_TYPE_MAPPING = [
        AttributeIdInterface::class => [
            AttributeId::class => ConfigHelper::NATIVE_DATA_TYPES,
            UuidInfra\AttributeId::class => ConfigHelper::UUID_DATA_TYPES,
        ],
        AttributeValueIdInterface::class => [
            AttributeValueId::class => ConfigHelper::NATIVE_DATA_TYPES,
            UuidInfra\AttributeValueId::class => ConfigHelper::UUID_DATA_TYPES,
        ],
    ];

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $ids = array_values(self::AGGREGATE_ROOTS);
        $entities = array_keys(self::IDENTITY_MAPPING);
        $requiredEntities = array_keys(self::REQUIRED_AGGREGATE_ROOTS);

        $treeBuilder->root(Extension::ALIAS)
            ->append(
                ConfigHelper::createClassMappingNode('class_mapping', $requiredEntities, $entities, true, function (array $value) use ($ids): array {
                    return $value + array_fill_keys($ids, null);
                })
            )
            ->append(
                ConfigHelper::createClassMappingNode('data_type_mapping', [], [], false, function ($value) use ($ids): array {
                    if (!is_array($value)) {
                        $value = array_fill_keys($ids, $value);
                    } else {
                        $value += array_fill_keys($ids, null);
                    }

                    return $value;
                })->addDefaultChildrenIfNoneSet($ids)
            );

        return $treeBuilder;
    }
}
