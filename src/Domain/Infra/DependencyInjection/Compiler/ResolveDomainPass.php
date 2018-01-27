<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Compiler;

use MsgPhp\Domain\{Factory, DomainIdentityMappingInterface, DomainMessageBusInterface};
use MsgPhp\Domain\Infra\{Doctrine as DoctrineInfra, InMemory as InMemoryInfra, SimpleBus as SimpleBusInfra};
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

        if ($container->has('doctrine.orm.entity_manager')) {
            self::register($container, DoctrineInfra\DomainIdentityMapping::class)
                ->setAutowired(true);

            self::alias($container, DomainIdentityMappingInterface::class, DoctrineInfra\DomainIdentityMapping::class);

            if (interface_exists(CacheWarmerInterface::class)) {
                self::register($container, DoctrineInfra\MappingCacheWarmer::class)
                    ->setArgument('$dirname', '%msgphp.doctrine.mapping_cache_dirname%')
                    ->setArgument('$mappingFiles', array_merge(...$container->getParameter('msgphp.doctrine.mapping_files')))
                    ->addTag('kernel.cache_warmer');
            }
        }

        if ($container->has('simple_bus.command_bus')) {
            self::register($container, SimpleBusInfra\DomainMessageBus::class)
                ->setArgument('$bus', new Reference('simple_bus.command_bus'));

            self::alias($container, DomainMessageBusInterface::class, SimpleBusInfra\DomainMessageBus::class);
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
        self::register($container, InMemoryInfra\ObjectFieldAccessor::class);

        self::register($container, InMemoryInfra\DomainIdentityMapping::class)
            ->setArgument('$mapping', array_merge(...$container->getParameter('msgphp.domain.identity_mapping')))
            ->setArgument('$accessor', new Reference(InMemoryInfra\ObjectFieldAccessor::class));

        self::alias($container, DomainIdentityMappingInterface::class, InMemoryInfra\DomainIdentityMapping::class);
    }

    private function registerEntityFactory(ContainerBuilder $container): void
    {
        self::register($container, Factory\DomainObjectFactory::class)
            ->addMethodCall('setNestedFactory', [new Reference(Factory\DomainObjectFactoryInterface::class)]);

        self::register($container, Factory\ClassMappingObjectFactory::class)
            ->setDecoratedService(Factory\DomainObjectFactory::class)
            ->setArgument('$mapping', array_merge(...$container->getParameter('msgphp.domain.class_mapping')))
            ->setArgument('$factory', new Reference(Factory\ClassMappingObjectFactory::class.'.inner'));

        self::register($container, Factory\EntityFactory::class)
            ->setArgument('$identifierMapping', array_merge(...$container->getParameter('msgphp.domain.id_class_mapping')))
            ->setArgument('$factory', new Reference(Factory\DomainObjectFactory::class));

        self::alias($container, Factory\DomainObjectFactoryInterface::class, Factory\DomainObjectFactory::class);
        self::alias($container, Factory\EntityFactoryInterface::class, Factory\EntityFactory::class);
    }
}
