<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Compiler;

use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManager;
use MsgPhp\Domain\{DomainIdentityHelper, DomainIdentityMappingInterface, Factory, Message};
use MsgPhp\Domain\Infra\DependencyInjection\ContainerHelper;
use MsgPhp\Domain\Infra\{Doctrine as DoctrineInfra, InMemory as InMemoryInfra, SimpleBus as SimpleBusInfra};
use SimpleBus\Message\Bus\MessageBus as SimpleMessageBus;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ResolveDomainPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $this->registerIdentityMapping($container);
        $this->registerEntityFactory($container);
        $this->registerMessageBus($container);

        if (interface_exists(CacheWarmerInterface::class) && $container->hasParameter($param = 'msgphp.doctrine.mapping_files')) {
            $mappingFiles = array_merge(...$container->getParameter($param));
            $container->getParameterBag()->remove($param);

            self::register($container, DoctrineInfra\MappingCacheWarmer::class)
                ->setArgument('$dirname', '%msgphp.doctrine.mapping_cache_dirname%')
                ->setArgument('$mappingFiles', $mappingFiles)
                ->addTag('kernel.cache_warmer');
        }
    }

    private static function register(ContainerBuilder $container, string $class, string $id = null): Definition
    {
        return $container->register($id ?? $class, $class)->setPublic(false);
    }

    private static function alias(ContainerBuilder $container, string $alias, string $id): void
    {
        $container->setAlias($alias, new Alias($id, false));
    }

    private function registerIdentityMapping(ContainerBuilder $container): void
    {
        $identityMapping = array_merge(...$container->getParameter($param = 'msgphp.domain.identity_mapping'));
        $container->getParameterBag()->remove($param);

        if ($container->has(DoctrineEntityManager::class)) {
            self::register($container, $alias = DoctrineInfra\DomainIdentityMapping::class)
                ->setAutowired(true);
        } else {
            self::register($container, InMemoryInfra\ObjectFieldAccessor::class);

            self::register($container, $alias = InMemoryInfra\DomainIdentityMapping::class)
                ->setArgument('$mapping', $identityMapping)
                ->setArgument('$accessor', new Reference(InMemoryInfra\ObjectFieldAccessor::class));
        }

        self::alias($container, DomainIdentityMappingInterface::class, $alias);

        self::register($container, DomainIdentityHelper::class)
            ->setAutowired(true);
    }

    private function registerEntityFactory(ContainerBuilder $container): void
    {
        $classMapping = array_merge(...$container->getParameter($param = 'msgphp.domain.class_mapping'));
        $container->getParameterBag()->remove($param);

        $idClassMapping = array_merge(...$container->getParameter($param = 'msgphp.domain.id_class_mapping'));
        $container->getParameterBag()->remove($param);

        self::register($container, Factory\DomainObjectFactory::class)
            ->addMethodCall('setNestedFactory', [new Reference(Factory\DomainObjectFactoryInterface::class)]);

        self::register($container, Factory\ClassMappingObjectFactory::class)
            ->setDecoratedService(Factory\DomainObjectFactory::class)
            ->setArgument('$factory', new Reference(Factory\ClassMappingObjectFactory::class.'.inner'))
            ->setArgument('$mapping', $classMapping);

        $entityFactory = self::register($container, Factory\EntityAwareFactory::class)
            ->setArgument('$factory', new Reference(Factory\DomainObjectFactory::class))
            ->setArgument('$identifierMapping', $idClassMapping);

        if ($container->has(DoctrineEntityManager::class)) {
            $entityFactory->setArgument('$referenceLoader', ContainerHelper::registerAnonymous($container, DoctrineInfra\EntityReferenceLoader::class)
                ->setAutowired(true)
                ->setArgument('$classMapping', $classMapping));
        }

        self::alias($container, Factory\DomainObjectFactoryInterface::class, Factory\DomainObjectFactory::class);
        self::alias($container, Factory\EntityAwareFactoryInterface::class, Factory\EntityAwareFactory::class);
    }

    private function registerMessageBus(ContainerBuilder $container): void
    {
        if (!($autowire = $container->has(SimpleMessageBus::class)) && !$container->has('simple_bus.command_bus')) {
            return;
        }

        $definition = self::register($container, SimpleBusInfra\DomainMessageBus::class);

        if ($autowire) {
            $definition->setAutowired(true);
        } else {
            $definition->setArgument('$bus', new Reference('simple_bus.command_bus'));
        }

        self::alias($container, Message\DomainMessageBusInterface::class, SimpleBusInfra\DomainMessageBus::class);
    }
}
