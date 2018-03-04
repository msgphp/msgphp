<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Compiler;

use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManager;
use MsgPhp\Domain\{DomainIdentityHelper, DomainIdentityMappingInterface, Factory, Message};
use MsgPhp\Domain\Infra\{Console as ConsoleInfra, Doctrine as DoctrineInfra, InMemory as InMemoryInfra, SimpleBus as SimpleBusInfra};
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
        $classMapping = array_merge(...$container->getParameter('msgphp.domain.class_mapping'));
        $idClassMapping = array_merge(...$container->getParameter('msgphp.domain.id_class_mapping'));
        $identityMapping = array_merge(...$container->getParameter('msgphp.domain.identity_mapping'));

        $this->registerIdentityMapping($container, $classMapping, $identityMapping);
        $this->registerEntityFactory($container, $classMapping, $idClassMapping);
        $this->registerMessageBus($container);

        if ($container->hasDefinition(ConsoleInfra\ContextBuilder\ClassContextBuilder::class)) {
            $container->getDefinition(ConsoleInfra\ContextBuilder\ClassContextBuilder::class)
                ->setArgument('$classMapping', $classMapping);
        }

        if (interface_exists(CacheWarmerInterface::class) && $container->hasParameter('msgphp.doctrine.mapping_files')) {
            $mappingFiles = array_merge(...$container->getParameter('msgphp.doctrine.mapping_files'));

            self::register($container, DoctrineInfra\MappingCacheWarmer::class)
                ->setArgument('$dirname', '%msgphp.doctrine.mapping_cache_dirname%')
                ->setArgument('$mappingFiles', $mappingFiles)
                ->addTag('kernel.cache_warmer', ['priority' => 100]);
        }

        if ($container->hasDefinition(DoctrineInfra\Event\ObjectFieldMappingListener::class)) {
            ($definition = $container->getDefinition(DoctrineInfra\Event\ObjectFieldMappingListener::class))
                ->setArgument('$mapping', self::processClassMapping($definition->getArgument('$mapping'), $classMapping));
        }

        $params = $container->getParameterBag();
        $params->remove('msgphp.domain.class_mapping');
        $params->remove('msgphp.domain.id_class_mapping');
        $params->remove('msgphp.domain.identity_mapping');
        $params->remove('msgphp.doctrine.mapping_files');
    }

    private static function register(ContainerBuilder $container, string $class, string $id = null): Definition
    {
        return $container->register($id ?? $class, $class)->setPublic(false);
    }

    private static function alias(ContainerBuilder $container, string $alias, string $id): void
    {
        $container->setAlias($alias, new Alias($id, false));
    }

    private static function processClassMapping($value, array $classMapping)
    {
        if (is_string($value) && isset($classMapping[$value])) {
            return $classMapping[$value];
        }

        if (is_array($value)) {
            array_walk_recursive($value, function (&$value) use ($classMapping): void {
                $value = self::processClassMapping($value, $classMapping);
            });
        }

        return $value;
    }

    private function registerIdentityMapping(ContainerBuilder $container, array $classMapping, array $identityMapping): void
    {
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

    private function registerEntityFactory(ContainerBuilder $container, array $classMapping, array $idClassMapping): void
    {
        self::register($container, $aliasId = Factory\DomainObjectFactory::class)
            ->addMethodCall('setNestedFactory', [new Reference(Factory\DomainObjectFactoryInterface::class)]);

        self::register($container, Factory\ClassMappingObjectFactory::class)
            ->setDecoratedService($aliasId)
            ->setArgument('$factory', new Reference(Factory\ClassMappingObjectFactory::class.'.inner'))
            ->setArgument('$mapping', $classMapping);

        self::register($container, $entityAliasId = Factory\EntityAwareFactory::class)
            ->setAutowired(true)
            ->setArgument('$identifierMapping', $idClassMapping);

        if ($container->has(DoctrineEntityManager::class)) {
            self::register($container, DoctrineInfra\EntityAwareFactory::class)
                ->setAutowired(true)
                ->setDecoratedService($entityAliasId)
                ->setArgument('$factory', new Reference(DoctrineInfra\EntityAwareFactory::class.'.inner'))
                ->setArgument('$classMapping', $classMapping);
        }

        self::alias($container, Factory\DomainObjectFactoryInterface::class, $aliasId);
        self::alias($container, Factory\EntityAwareFactoryInterface::class, $entityAliasId);
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
