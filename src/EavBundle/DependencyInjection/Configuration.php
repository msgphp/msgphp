<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle\DependencyInjection;

use MsgPhp\Eav\{AttributeIdInterface, AttributeValueIdInterface};
use MsgPhp\Eav\Entity\{Attribute, AttributeValue};
use MsgPhp\Eav\Infra\Uuid\{AttributeId, AttributeValueId};
use Ramsey\Uuid\Uuid;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(Extension::ALIAS);

        $rootNode
            ->children()
                ->arrayNode('class_mapping')
                    ->isRequired()
                    ->useAttributeAsKey('class')
                    ->scalarPrototype()
                        ->cannotBeEmpty()
                    ->end()
                    ->validate()
                        ->always()
                        ->then(function (array $value) {
                            if (class_exists(Uuid::class)) {
                                $value += [
                                    AttributeIdInterface::class => AttributeId::class,
                                    AttributeValueIdInterface::class => AttributeValueId::class,
                                ];
                            }

                            return $value;
                        })
                    ->end()
                    ->validate()
                        ->ifTrue(function (array $value) {
                            return !isset($value[Attribute::class]);
                        })
                        ->thenInvalid(sprintf('Class "%s" must be configured', Attribute::class))
                    ->end()
                    ->validate()
                        ->ifTrue(function (array $value) {
                            return !isset($value[AttributeValue::class]);
                        })
                        ->thenInvalid(sprintf('Class "%s" must be configured', AttributeValue::class))
                    ->end()
                    ->validate()
                        ->ifTrue(function (array $value) {
                            return !isset($value[AttributeIdInterface::class]);
                        })
                        ->thenInvalid(sprintf('Class "%s" must be configured. Try `composer require ramsey/uuid`', AttributeIdInterface::class))
                    ->end()
                    ->validate()
                        ->ifTrue(function (array $value) {
                            return !isset($value[AttributeValueIdInterface::class]);
                        })
                        ->thenInvalid(sprintf('Class "%s" must be configured. Try `composer require ramsey/uuid`', AttributeValueIdInterface::class))
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
