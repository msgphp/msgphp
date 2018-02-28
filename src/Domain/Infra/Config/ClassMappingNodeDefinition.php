<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder as BaseNodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Builder\VariableNodeDefinition;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\PrototypeNodeInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ClassMappingNodeDefinition extends VariableNodeDefinition implements ParentNodeDefinitionInterface
{
    public const NAME = 'class_mapping';

    /** @var BaseNodeBuilder|null */
    private $builder;
    private $prototype;
    private $type = 'scalar';

    public function requireClasses(array $classes): self
    {
        foreach ($classes as $class) {
            $this->validate()
                ->ifTrue(function (array $value) use ($class): bool {
                    return !isset($value[$class]);
                })
                ->thenInvalid(sprintf('Class mapping for "%s" must be configured.', $class));
        }

        if ($classes) {
            $this->isRequired();
        }

        return $this;
    }

    public function disallowClasses(array $classes): self
    {
        foreach ($classes as $class) {
            $this->validate()
                ->ifTrue(function (array $value) use ($class): bool {
                    return isset($value[$class]);
                })
                ->thenInvalid(sprintf('Class mapping for "%s" is not applicable.', $class));
        }

        return $this;
    }

    public function typeOfValues(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function subClassValues(): self
    {
        $this->validate()->always(function (array $value): array {
            foreach ($value as $class => $mappedClass) {
                if (!is_string($mappedClass)) {
                    throw new \LogicException(sprintf('Mapped value for class "%s" must be a string, got "%s".', $class, gettype($mappedClass)));
                }
                if (!is_subclass_of($mappedClass, $class)) {
                    throw new \LogicException(sprintf('Mapped class "%s" must be a sub class of "%s".', $mappedClass, $class));
                }
            }

            return $value;
        });

        return $this;
    }

    public function subClassKeys(array $classes): self
    {
        $this->validate()->always(function (array $value) use ($classes): array {
            foreach ($value as $class => $classValue) {
                foreach ($classes as $subClass) {
                    if (!is_subclass_of($class, $subClass)) {
                        throw new \LogicException(sprintf('Class "%s" must be a sub class of "%s".', $class, $subClass));
                    }
                }
            }

            return $value;
        });

        return $this;
    }

    public function defaultMapping(array $mapping): self
    {
        $this->defaultValue($mapping);
        $this->validate()->always(function (array $value) use ($mapping): array {
            return $value + $mapping;
        });

        return $this;
    }

    public function children(): BaseNodeBuilder
    {
        throw new \BadMethodCallException(sprintf('Method "%s" is not applicable.', __METHOD__));
    }

    public function append(NodeDefinition $node): self
    {
        throw new \BadMethodCallException(sprintf('Method "%s" is not applicable.', __METHOD__));
    }

    public function getChildNodeDefinitions(): array
    {
        throw new \BadMethodCallException(sprintf('Method "%s" is not applicable.', __METHOD__));
    }

    public function setBuilder(BaseNodeBuilder $builder): void
    {
        $this->builder = $builder;
    }

    /**
     * @return NodeParentInterface|BaseNodeBuilder|NodeDefinition|ArrayNodeDefinition|VariableNodeDefinition|NodeBuilder|self|null
     */
    public function end()
    {
        return $this->parent;
    }

    protected function instantiateNode(): ClassMappingNode
    {
        return new ClassMappingNode($this->name, $this->parent, $this->pathSeparator ?? '.');
    }

    protected function createNode(): NodeInterface
    {
        /** @var ClassMappingNode $node */
        $node = parent::createNode();
        $node->setKeyAttribute('class');

        $prototype = $this->getPrototype();
        $prototype->parent = $node;
        $prototypedNode = $prototype->getNode();

        if (!$prototypedNode instanceof PrototypeNodeInterface) {
            throw new \LogicException(sprintf('Prototyped node must be an instance of "%s", got "%s".', PrototypeNodeInterface::class, get_class($prototypedNode)));
        }

        $node->setPrototype($prototypedNode);

        return $node;
    }

    private function getPrototype(): NodeDefinition
    {
        if (null === $this->prototype) {
            $this->prototype = ($this->builder ?? new NodeBuilder())->node(null, $this->type);
            $this->prototype->setParent($this);
        }

        return $this->prototype;
    }
}
