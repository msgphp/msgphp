<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Config;

use Symfony\Component\Config\Definition\Builder\NodeBuilder as BaseNodeBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class NodeBuilder extends BaseNodeBuilder
{
    public function __construct()
    {
        parent::__construct();

        $this->nodeMapping[ClassMappingNodeDefinition::NAME] = ClassMappingNodeDefinition::class;
    }

    public function classMappingNode(string $name): ClassMappingNodeDefinition
    {
        return $this->node($name, ClassMappingNodeDefinition::NAME);
    }
}
