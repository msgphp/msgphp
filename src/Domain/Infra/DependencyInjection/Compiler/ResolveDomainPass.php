<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Compiler;

use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManager;
use MsgPhp\Domain\{DomainIdentityHelper, DomainIdentityMappingInterface, Factory, Message};
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

        if (interface_exists(CacheWarmerInterface::class) && $container->hasParameter('msgphp.doctrine.mapping_files')) {
            self::register($container, DoctrineInfra\MappingCacheWarmer::class)
                ->setArgument('$dirname', '%msgphp.doctrine.mapping_cache_dirname%')
                ->setArgument('$mappingFiles', array_merge(...$container->getParameter('msgphp.doctrine.mapping_files')))
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
        $identityMapping = $container->getParameter($param = 'msgphp.domain.identity_mapping');
        $container->getParameterBag()->remove($param);

        if ($container->has(DoctrineEntityManager::class)) {
            self::register($container, $alias = DoctrineInfra\DomainIdentityMapping::class)
                ->setAutowired(true);
        } else {
            self::register($container, InMemoryInfra\ObjectFieldAccessor::class);

            self::register($container, $alias = InMemoryInfra\DomainIdentityMapping::class)
                ->setArgument('$mapping', array_merge(...$identityMapping))
                ->setArgument('$accessor', new Reference(InMemoryInfra\ObjectFieldAccessor::class));
        }

        self::alias($container, DomainIdentityMappingInterface::class, $alias);

        self::register($container, DomainIdentityHelper::class)
            ->setAutowired(true);
    }

    private function registerEntityFactory(ContainerBuilder $container): void
    {
        $classMapping = $container->getParameter($param = 'msgphp.domain.class_mapping');
        $container->getParameterBag()->remove($param);

        $idClassMapping = $container->getParameter($param = 'msgphp.domain.id_class_mapping');
        $container->getParameterBag()->remove($param);

        self::register($container, Factory\DomainObjectFactory::class)
            ->addMethodCall('setNestedFactory', [new Reference(Factory\DomainObjectFactoryInterface::class)]);

        self::register($container, Factory\ClassMappingObjectFactory::class)
            ->setDecoratedService(Factory\DomainObjectFactory::class)
            ->setArgument('$mapping', array_merge(...$classMapping))
            ->setArgument('$factory', new Reference(Factory\ClassMappingObjectFactory::class.'.inner'));

        self::register($container, Factory\EntityAwareFactory::class)
            ->setArgument('$identifierMapping', array_merge(...$idClassMapping))
            ->setArgument('$factory', new Reference(Factory\DomainObjectFactory::class));

        self::alias($container, Factory\DomainObjectFactoryInterface::class, Factory\EntityAwareFactory::class);
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
