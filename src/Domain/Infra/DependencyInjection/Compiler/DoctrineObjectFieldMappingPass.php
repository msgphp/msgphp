<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Compiler;

use MsgPhp\Domain\Infra\DependencyInjection\Bundle\ContainerHelper;
use MsgPhp\Domain\Infra\Doctrine\EntityFieldsMapping;
use MsgPhp\Domain\Infra\Doctrine\Event\ObjectFieldMappingListener;
use MsgPhp\Domain\Infra\Doctrine\ObjectFieldMappingProviderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DoctrineObjectFieldMappingPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    private $tagName;
    private $defaultProviders;

    public function __construct(string $tagName = 'msgphp.doctrine.object_field_mapping', array $defaultProviders = [EntityFieldsMapping::class])
    {
        $this->tagName = $tagName;
        $this->defaultProviders = $defaultProviders;
    }

    public function process(ContainerBuilder $container): void
    {
        $mapping = [];
        $providers = array_merge($this->defaultProviders, array_map(function (Reference $provider) use ($container): ?string {
            return $container->findDefinition((string) $provider)->getClass();
        }, $this->findAndSortTaggedServices($this->tagName, $container)));

        foreach ($providers as $provider) {
            if (!ContainerHelper::getClassReflection($container, $provider)->implementsInterface(ObjectFieldMappingProviderInterface::class)) {
                throw new InvalidArgumentException(sprintf('Provider "%s" must implement interface "%s".', $provider, ObjectFieldMappingProviderInterface::class));
            }

            $mapping = array_replace_recursive($mapping, $provider::getObjectFieldMapping());
        }

        if (!$mapping) {
            $container->removeDefinition(ObjectFieldMappingListener::class);

            return;
        }

        $container->getDefinition(ObjectFieldMappingListener::class)
            ->setArgument('$mapping', $mapping);
    }
}
