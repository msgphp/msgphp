<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle\DependencyInjection;

use MsgPhp\Domain\Infra\DependencyInjection\Bundle\ConfigHelper;
use MsgPhp\Eav\{AttributeId, AttributeIdInterface, AttributeValueId, AttributeValueIdInterface};
use MsgPhp\Eav\Entity\{Attribute, AttributeValue};
use MsgPhp\Eav\Infra\Uuid;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Configuration implements ConfigurationInterface
{
    public const IDENTITY_MAP = [
        Attribute::class => 'id',
        AttributeValue::class => 'id',
    ];
    public const DATA_TYPE_MAP = [
        AttributeIdInterface::class => [
            AttributeId::class => ConfigHelper::NATIVE_DATA_TYPES,
            Uuid\AttributeId::class => ConfigHelper::UUID_DATA_TYPES,
        ],
        AttributeValueIdInterface::class => [
            AttributeValueId::class => ConfigHelper::NATIVE_DATA_TYPES,
            Uuid\AttributeValueId::class => ConfigHelper::UUID_DATA_TYPES,
        ],
    ];
    public const REQUIRED_AGGREGATE_ROOTS = [
        Attribute::class => AttributeIdInterface::class,
        AttributeValue::class => AttributeValueIdInterface::class,
    ];
    public const OPTIONAL_AGGREGATE_ROOTS = [];
    public const AGGREGATE_ROOTS = self::REQUIRED_AGGREGATE_ROOTS + self::OPTIONAL_AGGREGATE_ROOTS;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $requiredEntities = array_keys(self::REQUIRED_AGGREGATE_ROOTS);
        $availableIds = array_values(self::AGGREGATE_ROOTS);

        $treeBuilder->root(Extension::ALIAS)
            ->append(
                ConfigHelper::createClassMappingNode('class_mapping', $requiredEntities, function (array $value) use ($availableIds): array {
                    return $value + array_fill_keys($availableIds, null);
                })
            )
            ->append(
                ConfigHelper::createClassMappingNode('data_type_mapping', [], function ($value) use ($availableIds) {
                    if (!is_array($value)) {
                        $value = array_fill_keys($availableIds, $value);
                    } else {
                        $value += array_fill_keys($availableIds, null);
                    }

                    return $value;
                })
                ->addDefaultChildrenIfNoneSet($availableIds)
            );

        return $treeBuilder;
    }
}
