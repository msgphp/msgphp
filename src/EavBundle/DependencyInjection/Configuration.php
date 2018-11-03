<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle\DependencyInjection;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Infra\Config\{NodeBuilder, TreeBuilder};
use MsgPhp\Domain\Infra\DependencyInjection\ConfigHelper;
use MsgPhp\Eav\{AttributeId, AttributeIdInterface, AttributeValueId, AttributeValueIdInterface, Command, Entity};
use MsgPhp\Eav\Infra\{Doctrine as DoctrineInfra, Uuid as UuidInfra};
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    public const PACKAGE_NS = 'MsgPhp\\Eav\\';
    public const AGGREGATE_ROOTS = [
        Entity\Attribute::class => AttributeIdInterface::class,
        Entity\AttributeValue::class => AttributeValueIdInterface::class,
    ];
    public const IDENTITY_MAPPING = [
        Entity\Attribute::class => ['id'],
        Entity\AttributeValue::class => ['id'],
    ];
    public const DOCTRINE_TYPE_MAPPING = [
        AttributeIdInterface::class => DoctrineInfra\Type\AttributeIdType::class,
        AttributeValueIdInterface::class => DoctrineInfra\Type\AttributeValueIdType::class,
    ];
    public const DOCTRINE_REPOSITORY_MAPPING = [
        Entity\Attribute::class => DoctrineInfra\Repository\AttributeRepository::class,
    ];
    private const DEFAULT_ID_MAPPING = [
        AttributeIdInterface::class => AttributeId::class,
        AttributeValueIdInterface::class => AttributeValueId::class,
    ];
    private const UUID_MAPPING = [
        AttributeIdInterface::class => UuidInfra\AttributeId::class,
        AttributeValueIdInterface::class => UuidInfra\AttributeValueId::class,
    ];
    private const COMMAND_MAPPING = [
        Entity\Attribute::class => [
            Command\CreateAttributeCommand::class => true,
            Command\DeleteAttributeCommand::class => true,
        ],
    ];

    private static $packageDirs;

    /**
     * @return string[]
     */
    public static function getPackageDirs(): array
    {
        if (null !== self::$packageDirs) {
            return self::$packageDirs;
        }

        return self::$packageDirs = [
            \dirname((string) (new \ReflectionClass(AttributeIdInterface::class))->getFileName()),
        ];
    }

    public static function getPackageGlob(): string
    {
        $dirs = self::getPackageDirs();

        return isset($dirs[1]) ? '{'.implode(',', $dirs).'}' : $dirs[0];
    }

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
            ->classMappingNode('commands')
                ->typeOfValues('boolean')
            ->end()
            ->scalarNode('default_id_type')
                ->defaultValue(ConfigHelper::DEFAULT_ID_TYPE)
                ->cannotBeEmpty()
            ->end()
        ->end()
        ->validate()
            ->always(ConfigHelper::defaultBundleConfig(
                self::DEFAULT_ID_MAPPING,
                array_fill_keys(ConfigHelper::UUID_TYPES, self::UUID_MAPPING)
            ))
        ->end()
        ->validate()
            ->always(function (array $config): array {
                ConfigHelper::resolveCommandMappingConfig(self::COMMAND_MAPPING, $config['class_mapping'], $config['commands']);

                return $config;
            })
        ->end();

        return $treeBuilder;
    }
}
