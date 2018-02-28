<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder as BaseNodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder as BaseTreeBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class TreeBuilder extends BaseTreeBuilder
{
    public function rootArray(string $name, BaseNodeBuilder $builder = null): ArrayNodeDefinition
    {
        /** @var ArrayNodeDefinition $node */
        $node = $this->root($name, 'array', $builder ?? new NodeBuilder());

        return $node;
    }
}
