<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Config;

use Symfony\Component\Config\Definition\Builder\NodeBuilder as BaseNodeBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class NodeBuilder extends BaseNodeBuilder
{
    public function __construct()
    {
        parent::__construct();

        $this->setNodeClass(ClassMappingNodeDefinition::NAME, ClassMappingNodeDefinition::class);
    }

    public function classMappingNode(string $name): ClassMappingNodeDefinition
    {
        /** @var ClassMappingNodeDefinition $node */
        $node = $this->node($name, ClassMappingNodeDefinition::NAME);

        return $node;
    }
}
