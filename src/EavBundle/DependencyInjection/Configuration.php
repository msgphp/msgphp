<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle\DependencyInjection;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Infra\Config\{NodeBuilder, TreeBuilder};
use MsgPhp\Domain\Infra\DependencyInjection\ConfigHelper;
use MsgPhp\Eav\{AttributeId, AttributeIdInterface, AttributeValueId, AttributeValueIdInterface, Entity};
use MsgPhp\Eav\Infra\Uuid as UuidInfra;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    public const AGGREGATE_ROOTS = [
        Entity\Attribute::class => AttributeIdInterface::class,
        Entity\AttributeValue::class => AttributeValueIdInterface::class,
    ];
    public const IDENTITY_MAPPING = [
        Entity\Attribute::class => ['id'],
        Entity\AttributeValue::class => ['id'],
    ];
    public const DEFAULT_ID_CLASS_MAPPING = [
        AttributeIdInterface::class => AttributeId::class,
        AttributeValueIdInterface::class => AttributeValueId::class,
    ];
    public const UUID_CLASS_MAPPING = [
        AttributeIdInterface::class => UuidInfra\AttributeId::class,
        AttributeValueIdInterface::class => UuidInfra\AttributeValueId::class,
    ];

    public function getConfigTreeBuilder(): TreeBuilder
    {
        /** @var NodeBuilder $children */
        $children = ($treeBuilder = new TreeBuilder())->rootArray(Extension::ALIAS)->children();

        $children
            ->classMappingNode('class_mapping')
                ->requireClasses([Entity\Attribute::class, Entity\AttributeValue::class])
                ->subClassValues()
            ->end()
            ->classMappingNode('id_type_mapping')
                ->subClassKeys([DomainIdInterface::class])
            ->end()
            ->scalarNode('default_id_type')
                ->defaultValue(ConfigHelper::DEFAULT_ID_TYPE)
                ->cannotBeEmpty()
            ->end()
        ->end()
        ->validate()
            ->always(ConfigHelper::defaultBundleConfig(
                self::DEFAULT_ID_CLASS_MAPPING,
                array_fill_keys(ConfigHelper::UUID_TYPES, self::UUID_CLASS_MAPPING)
            ))
        ->end();

        return $treeBuilder;
    }
}
