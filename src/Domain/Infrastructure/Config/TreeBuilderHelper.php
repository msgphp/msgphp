<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class TreeBuilderHelper
{
    public static function create(string $name): TreeBuilder
    {
        return new TreeBuilder($name, 'array', new NodeBuilder());
    }

    public static function root(TreeBuilder $treeBuilder, string $name): ArrayNodeDefinition
    {
        /**
         * @psalm-suppress UndefinedMethod
         *
         * @var ArrayNodeDefinition
         */
        return method_exists($treeBuilder, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root($name, 'array', new NodeBuilder());
    }
}
