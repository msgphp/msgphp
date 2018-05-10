<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Compiler;

use MsgPhp\Domain\Infra\Doctrine\ObjectFieldMappingsProviderInterface;
use MsgPhp\Domain\Infra\Doctrine\Event\ObjectFieldMappingListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DoctrineObjectFieldMappingPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    private $tagName;
    private $listenerId;

    public function __construct(string $tagName = 'msgphp.doctrine.object_field_mappings', string $listenerId = ObjectFieldMappingListener::class)
    {
        $this->tagName = $tagName;
        $this->listenerId = $listenerId;
    }

    public function process(ContainerBuilder $container): void
    {
        $mappings = [];

        foreach ($this->findAndSortTaggedServices($this->tagName, $container) as $providerId) {
            $providerId = (string) $providerId;
            $providerClass = $container->findDefinition($providerId)->getClass() ?? $providerId;

            if (!is_subclass_of($providerClass, ObjectFieldMappingsProviderInterface::class)) {
                throw new InvalidArgumentException(sprintf('Provider "%s" must implement "%s".', $providerId, ObjectFieldMappingsProviderInterface::class));
            }

            foreach ($providerClass::provideObjectFieldMappings() as $class => $mapping) {
                if (isset($mappings[$class])) {
                    continue;
                }

                $mappings[$class] = $mapping;
            }
        }

        if ($mappings) {
            $container->getDefinition($this->listenerId)
                ->setArgument('$mappings', $mappings);
        } else {
            $container->removeDefinition($this->listenerId);
        }
    }
}
