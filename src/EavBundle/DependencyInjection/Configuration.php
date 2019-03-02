<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle\DependencyInjection;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Infra\Config\{NodeBuilder, TreeBuilderHelper};
use MsgPhp\Domain\Infra\DependencyInjection\{ConfigHelper, PackageMetadata};
use MsgPhp\Eav\{AttributeId, AttributeIdInterface, AttributeValueId, AttributeValueIdInterface, Command, Entity};
use MsgPhp\Eav\Infra\{Doctrine as DoctrineInfra, Uuid as UuidInfra};
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    public const PACKAGE_NS = 'MsgPhp\\Eav\\';
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
    private const ID_TYPE_MAPPING = [
        AttributeIdInterface::class => ['scalar' => AttributeId::class, 'uuid' => UuidInfra\AttributeId::class],
        AttributeValueIdInterface::class => ['scalar' => AttributeValueId::class, 'uuid' => UuidInfra\AttributeValueId::class],
    ];
    private const COMMAND_MAPPING = [
        Entity\Attribute::class => [
            Command\CreateAttributeCommand::class => true,
            Command\DeleteAttributeCommand::class => true,
        ],
    ];

    /**
     * @var PackageMetadata|null
     */
    private static $packageMetadata;

    public static function getPackageMetadata(): PackageMetadata
    {
        if (null !== self::$packageMetadata) {
            return self::$packageMetadata;
        }

        return self::$packageMetadata = new PackageMetadata(self::PACKAGE_NS, [
            \dirname((string) (new \ReflectionClass(AttributeIdInterface::class))->getFileName()),
        ]);
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        /** @var NodeBuilder $children */
        $children = TreeBuilderHelper::root(Extension::ALIAS, $treeBuilder)->children();
        /** @psalm-suppress PossiblyNullReference */
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
            ->always(ConfigHelper::defaultBundleConfig(self::ID_TYPE_MAPPING))
        ->end()
        ->validate()
            ->always(function (array $config): array {
                ConfigHelper::resolveCommandMappingConfig(self::COMMAND_MAPPING, $config['class_mapping'], $config['commands']);

                return $config;
            })
        ->end()
        ;

        return $treeBuilder;
    }
}
