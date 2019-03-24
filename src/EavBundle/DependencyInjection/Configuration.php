<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle\DependencyInjection;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Infrastructure\Config\NodeBuilder;
use MsgPhp\Domain\Infrastructure\Config\TreeBuilderHelper;
use MsgPhp\Domain\Infrastructure\DependencyInjection\ConfigHelper;
use MsgPhp\Domain\Infrastructure\DependencyInjection\PackageMetadata;
use MsgPhp\Eav\Attribute;
use MsgPhp\Eav\AttributeIdInterface;
use MsgPhp\Eav\AttributeValue;
use MsgPhp\Eav\AttributeValueIdInterface;
use MsgPhp\Eav\Command;
use MsgPhp\Eav\Infrastructure\Doctrine as DoctrineInfrastructure;
use MsgPhp\Eav\Infrastructure\Uuid as UuidInfrastructure;
use MsgPhp\Eav\ScalarAttributeId;
use MsgPhp\Eav\ScalarAttributeValueId;
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
    public const DOCTRINE_TYPE_MAPPING = [
        AttributeIdInterface::class => DoctrineInfrastructure\Type\AttributeIdType::class,
        AttributeValueIdInterface::class => DoctrineInfrastructure\Type\AttributeValueIdType::class,
    ];
    public const DOCTRINE_REPOSITORY_MAPPING = [
        Attribute::class => DoctrineInfrastructure\Repository\AttributeRepository::class,
    ];
    private const ID_TYPE_MAPPING = [
        AttributeIdInterface::class => [
            'scalar' => ScalarAttributeId::class,
            'uuid' => UuidInfrastructure\AttributeUuid::class,
        ],
        AttributeValueIdInterface::class => [
            'scalar' => ScalarAttributeValueId::class,
            'uuid' => UuidInfrastructure\AttributeValueUuid::class,
        ],
    ];
    private const COMMAND_MAPPING = [
        Attribute::class => [
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
                ->requireClasses([Attribute::class, AttributeValue::class])
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
