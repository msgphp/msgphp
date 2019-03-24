<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Config;

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

    /**
     * @var BaseNodeBuilder|null
     */
    private $builder;

    /**
     * @var NodeDefinition|null
     */
    private $prototype;

    /**
     * @var string
     */
    private $type = 'scalar';

    /**
     * @psalm-var array<class-string, string>
     */
    private $hints = [];

    public function requireClasses(array $classes): self
    {
        foreach ($classes as $class) {
            $this->validate()
                ->ifTrue(function (array $value) use ($class): bool {
                    return !isset($value[$class]);
                })
                ->thenInvalid(sprintf('Class "%s" must be configured.', $class))
            ;
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
                ->thenInvalid(sprintf('Class "%s" is not applicable to be configured.', $class))
            ;
        }

        return $this;
    }

    public function groupClasses(array $classes): self
    {
        $this->validate()->always(function (array $value) use ($classes): array {
            if ($classes !== ($missing = array_diff($classes, array_keys($value))) && $missing) {
                foreach (array_diff($classes, $missing) as $known) {
                    throw new \LogicException(sprintf('Class "%s" requires "%s" to be configured.', $known, implode('", "', $missing)));
                }
            }

            return $value;
        });

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
                if (!\is_string($mappedClass)) {
                    throw new \LogicException(sprintf('Class "%s" must be configured to a mapped sub class value, got type "%s".', $class, \gettype($mappedClass)));
                }
                if (!is_subclass_of($mappedClass, $class)) {
                    throw new \LogicException(sprintf('Class "%s" must be configured to a mapped sub class value, got "%s".', $class, $mappedClass));
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

    /**
     * @psalm-param class-string|array<int, class-string> $class
     *
     * @param string|string[] $class
     */
    public function hint($class, string $hint): self
    {
        foreach ((array) $class as $class) {
            $this->hints[$class] = $hint;
        }

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

    /**
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    protected function instantiateNode(): ClassMappingNode
    {
        return new ClassMappingNode($this->name, $this->parent, $this->pathSeparator ?? '.');
    }

    protected function createNode(): NodeInterface
    {
        /** @var ClassMappingNode $node */
        $node = parent::createNode();
        $node->setKeyAttribute('class');
        $node->setHints($this->hints);

        $prototype = $this->getPrototype();
        $prototype->parent = $node;
        $prototypedNode = $prototype->getNode();

        if (!$prototypedNode instanceof PrototypeNodeInterface) {
            throw new \LogicException(sprintf('Prototyped node must be an instance of "%s", got "%s".', PrototypeNodeInterface::class, \get_class($prototypedNode)));
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
