<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Compiler;

use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManager;
use MsgPhp\Domain\{DomainIdentityHelper, DomainIdentityMappingInterface, Factory, Message};
use MsgPhp\Domain\Infra\DependencyInjection\ContainerHelper;
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
        $this->registerMessageBus($container);

        if (interface_exists(CacheWarmerInterface::class) && $container->hasParameter('msgphp.doctrine.mapping_files')) {
            $mappingFiles = array_merge(...$container->getParameter('msgphp.doctrine.mapping_files'));

            self::register($container, DoctrineInfra\MappingCacheWarmer::class)
                ->setArgument('$dirname', '%msgphp.doctrine.mapping_cache_dirname%')
                ->setArgument('$mappingFiles', $mappingFiles)
                ->addTag('kernel.cache_warmer', ['priority' => 100]);
        }

        $params = $container->getParameterBag();
        $params->remove('msgphp.doctrine.mapping_files');
        $params->remove('msgphp.doctrine.identity_mapping');
        $params->remove('msgphp.doctrine.class_mapping');
        $params->remove('msgphp.doctrine.id_class_mapping');
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
        $identityMapping = array_merge(...$container->getParameter('msgphp.domain.identity_mapping'));
        $classMapping = array_merge(...$container->getParameter('msgphp.domain.class_mapping'));

        if ($container->has(DoctrineEntityManager::class)) {
            self::register($container, $aliasId = DoctrineInfra\DomainIdentityMapping::class)
                ->setAutowired(true)
                ->setArgument('$classMapping', $classMapping);
        } else {
            self::register($container, $aliasId = InMemoryInfra\DomainIdentityMapping::class)
                ->setArgument('$mapping', $identityMapping)
                ->setArgument('$accessor', self::register($container, InMemoryInfra\ObjectFieldAccessor::class)
                    ->setAutowired(true));
        }

        self::alias($container, DomainIdentityMappingInterface::class, $aliasId);

        self::register($container, DomainIdentityHelper::class)
            ->setAutowired(true);
    }

    private function registerEntityFactory(ContainerBuilder $container): void
    {
        $classMapping = array_merge(...$container->getParameter('msgphp.domain.class_mapping'));
        $idClassMapping = array_merge(...$container->getParameter('msgphp.domain.id_class_mapping'));

        self::register($container, $aliasId = Factory\DomainObjectFactory::class)
            ->addMethodCall('setNestedFactory', [new Reference(Factory\DomainObjectFactoryInterface::class)]);

        self::register($container, Factory\ClassMappingObjectFactory::class)
            ->setDecoratedService($aliasId)
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

        self::alias($container, Factory\DomainObjectFactoryInterface::class, $aliasId);
        self::alias($container, Factory\EntityAwareFactoryInterface::class, Factory\EntityAwareFactory::class);
    }

    private function registerMessageBus(ContainerBuilder $container): void
    {
        if (!$container->has('simple_bus.command_bus')) {
            return;
        }

        self::register($container, $aliasId = SimpleBusInfra\DomainMessageBus::class)
            ->setArgument('$bus', new Reference('simple_bus.command_bus'));

        self::alias($container, Message\DomainMessageBusInterface::class, $aliasId);
    }
}
