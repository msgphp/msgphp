<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle\DependencyInjection;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Infrastructure\Config\NodeBuilder;
use MsgPhp\Domain\Infrastructure\Config\TreeBuilderHelper;
use MsgPhp\Domain\Infrastructure\DependencyInjection\ConfigHelper;
use MsgPhp\Domain\Infrastructure\DependencyInjection\PackageMetadata;
use MsgPhp\Eav\Attribute;
use MsgPhp\Eav\AttributeId;
use MsgPhp\Eav\AttributeValue;
use MsgPhp\Eav\AttributeValueId;
use MsgPhp\Eav\Command;
use MsgPhp\Eav\Infrastructure\Doctrine as DoctrineInfrastructure;
use MsgPhp\Eav\Infrastructure\Uid as UidInfrastructure;
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
    public const DOCTRINE_REPOSITORY_MAPPING = [
        Attribute::class => DoctrineInfrastructure\Repository\AttributeRepository::class,
    ];
    private const COMMAND_MAPPING = [
        Attribute::class => [
            Command\CreateAttribute::class => true,
            Command\DeleteAttribute::class => true,
        ],
    ];

    /** @var PackageMetadata|null */
    private static $packageMetadata;

    public static function getPackageMetadata(): PackageMetadata
    {
        if (null !== self::$packageMetadata) {
            return self::$packageMetadata;
        }

        return self::$packageMetadata = new PackageMetadata(self::PACKAGE_NS, [
            \dirname((string) (new \ReflectionClass(AttributeId::class))->getFileName()),
        ]);
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        /** @var NodeBuilder $children */
        $children = TreeBuilderHelper::root(Extension::ALIAS, $treeBuilder)->children();
        /**
         * @psalm-suppress PossiblyNullReference
         * @psalm-suppress PossiblyUndefinedMethod
         */
        $children
            ->classMappingNode('class_mapping')
                ->requireClasses([Attribute::class, AttributeValue::class])
                ->subClassValues()
            ->end()
            ->classMappingNode('id_type_mapping')
                ->subClassKeys([DomainId::class])
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
            ->always(static function (array $config): array {
                ConfigHelper::resolveCommandMappingConfig(self::COMMAND_MAPPING, $config['class_mapping'], $config['commands']);

                return $config;
            })
        ->end()
        ;

        return $treeBuilder;
    }
}
