<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Compiler;

use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Domain\{CommandBusInterface, Factory, DomainIdentityMapInterface, EventBusInterface};
use MsgPhp\Domain\Infra\DependencyInjection\Bundle\ContainerHelper;
use MsgPhp\Domain\Infra\{Doctrine as DoctrineInfra, InMemory as InMemoryInfra, SimpleBus as SimpleBusInfra};
use SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle;
use SimpleBus\SymfonyBridge\SimpleBusEventBusBundle;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ResolveDomainPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $this->processIdentityMap($container);
        $this->processEntityFactory($container);

        $bundles = ContainerHelper::getBundles($container);

        if (ContainerHelper::isDoctrineOrmEnabled($container)) {
            self::register($container, DoctrineInfra\DomainIdentityMap::class)
                ->setArgument('$em', new Reference(EntityManagerInterface::class));

            self::alias($container, DomainIdentityMapInterface::class, DoctrineInfra\DomainIdentityMap::class);

            self::register($container, DoctrineInfra\MappingCacheWarmer::class)
                ->setArgument('$dirname', '%msgphp.doctrine.mapping_cache_dirname%')
                ->setArgument('$mappingFiles', array_merge(...$container->getParameter('msgphp.doctrine.mapping_files')))
                ->addTag('kernel.cache_warmer');
        }

        if (isset($bundles[SimpleBusCommandBusBundle::class])) {
            self::register($container, SimpleBusInfra\DomainCommandBus::class)
                ->setArgument('$messageBus', new Reference('command_bus'));

            self::alias($container, CommandBusInterface::class, SimpleBusInfra\DomainCommandBus::class);
        }

        if (isset($bundles[SimpleBusEventBusBundle::class])) {
            self::register($container, SimpleBusInfra\DomainEventBus::class)
                ->setArgument('$messageBus', new Reference('event_bus'));

            self::alias($container, EventBusInterface::class, SimpleBusInfra\DomainEventBus::class);
        }
    }

    private static function register(ContainerBuilder $container, string $class): Definition
    {
        return $container->register($class, $class)->setPublic(false);
    }

    private static function alias(ContainerBuilder $container, string $alias, string $id): void
    {
        $container->setAlias($alias, new Alias($id, false));
    }

    private function processIdentityMap(ContainerBuilder $container): void
    {
        self::register($container, InMemoryInfra\ObjectFieldAccessor::class);

        self::register($container, InMemoryInfra\DomainIdentityMap::class)
            ->setArgument('$mapping', array_merge(...$container->getParameter('msgphp.domain.identity_map')))
            ->setArgument('$accessor', new Reference(InMemoryInfra\ObjectFieldAccessor::class));

        self::alias($container, DomainIdentityMapInterface::class, InMemoryInfra\DomainIdentityMap::class);
    }

    private function processEntityFactory(ContainerBuilder $container): void
    {
        self::register($container, Factory\ConstructorResolvingObjectFactory::class)
            ->addMethodCall('setNestedFactory', [new Reference(Factory\EntityFactoryInterface::class)]);

        self::register($container, Factory\ClassMappingObjectFactory::class)
            ->setArgument('$mapping', array_merge(...$container->getParameter('msgphp.domain.class_map')))
            ->setArgument('$factory', new Reference(Factory\ConstructorResolvingObjectFactory::class));

        self::register($container, Factory\EntityFactory::class)
            ->setArgument('$identifierMapping', array_merge(...$container->getParameter('msgphp.domain.id_class_map')))
            ->setArgument('$factory', new Reference(Factory\ClassMappingObjectFactory::class));

        self::alias($container, Factory\EntityFactoryInterface::class, Factory\EntityFactory::class);
    }
}
