<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Compiler;

use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManager;
use MsgPhp\Domain\{DomainIdentityHelper, DomainIdentityMappingInterface, Factory, Message};
use MsgPhp\Domain\Infra\{Console as ConsoleInfra, Doctrine as DoctrineInfra, InMemory as InMemoryInfra, Messenger as MessengerInfra, SimpleBus as SimpleBusInfra};
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ResolveDomainPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->setParameter('msgphp.domain.class_mapping', $classMapping = array_merge(...$container->getParameter('msgphp.domain.class_mapping')));
        $idClassMapping = array_merge(...$container->getParameter('msgphp.domain.id_class_mapping'));
        $identityMapping = array_merge(...$container->getParameter('msgphp.domain.identity_mapping'));

        $this->registerIdentityMapping($container, $identityMapping);
        $this->registerEntityFactory($container, $idClassMapping);
        $this->registerMessageBus($container);

        if (interface_exists(CacheWarmerInterface::class) && $container->hasParameter('msgphp.doctrine.mapping_files')) {
            self::register($container, DoctrineInfra\MappingCacheWarmer::class)
                ->setArgument('$dirName', 'msgphp/doctrine-mapping')
                ->setArgument('$mappingFiles', array_merge(...$container->getParameter('msgphp.doctrine.mapping_files')))
                ->addTag('kernel.cache_warmer', ['priority' => 100]);
        }

        foreach ($container->findTaggedServiceIds('msgphp.domain.process_class_mapping') as $id => $attr) {
            $definition = $container->getDefinition($id);

            foreach ($attr as $attr) {
                if (!isset($attr['argument'])) {
                    continue;
                }

                $value = $definition->getArgument($attr['argument']);
                $definition->setArgument($attr['argument'], self::processClassMapping($value, $classMapping, !empty($attr['array_keys'])));
            }

            $definition->clearTag('msgphp.domain.process_class_mapping');
        }

        $params = $container->getParameterBag();
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

    private static function processClassMapping($value, array $classMapping, bool $arrayKeys = false)
    {
        if (is_string($value) && isset($classMapping[$value])) {
            return $classMapping[$value];
        }

        if (!is_array($value)) {
            return $value;
        }

        $result = [];

        foreach ($value as $k => $v) {
            $v = self::processClassMapping($v, $classMapping, $arrayKeys);
            if ($arrayKeys) {
                $k = self::processClassMapping($k, $classMapping);
            }

            $result[$k] = $v;
        }

        return $result;
    }

    private function registerIdentityMapping(ContainerBuilder $container, array $identityMapping): void
    {
        if ($container->has(DoctrineEntityManager::class)) {
            self::register($container, $aliasId = DoctrineInfra\DomainIdentityMapping::class)
                ->setAutowired(true)
                ->setArgument('$classMapping', '%msgphp.domain.class_mapping%');
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

    private function registerEntityFactory(ContainerBuilder $container, array $idClassMapping): void
    {
        self::register($container, $aliasId = Factory\DomainObjectFactory::class)
            ->addMethodCall('setNestedFactory', [new Reference(Factory\DomainObjectFactoryInterface::class)]);

        self::register($container, Factory\ClassMappingObjectFactory::class)
            ->setDecoratedService($aliasId)
            ->setArgument('$factory', new Reference(Factory\ClassMappingObjectFactory::class.'.inner'))
            ->setArgument('$mapping', '%msgphp.domain.class_mapping%');

        self::register($container, $entityAliasId = Factory\EntityAwareFactory::class)
            ->setAutowired(true)
            ->setArgument('$identifierMapping', $idClassMapping);

        if ($container->has(DoctrineEntityManager::class)) {
            self::register($container, DoctrineInfra\EntityAwareFactory::class)
                ->setAutowired(true)
                ->setDecoratedService($entityAliasId)
                ->setArgument('$factory', new Reference(DoctrineInfra\EntityAwareFactory::class.'.inner'))
                ->setArgument('$classMapping', '%msgphp.domain.class_mapping%');
        }

        self::alias($container, Factory\DomainObjectFactoryInterface::class, $aliasId);
        self::alias($container, Factory\EntityAwareFactoryInterface::class, $entityAliasId);
    }

    private function registerMessageBus(ContainerBuilder $container): void
    {
        $aliasId = null;

        if ($container->has('simple_bus.command_bus')) {
            self::register($container, $aliasId = SimpleBusInfra\DomainMessageBus::class)
                ->setArgument('$bus', new Reference('simple_bus.command_bus'));
        }

        if ($container->has(MessageBusInterface::class)) {
            self::register($container, $aliasId = MessengerInfra\DomainMessageBus::class)
                ->setAutowired(true);

            if (class_exists(ConsoleEvents::class)) {
                foreach ($container->findTaggedServiceIds('messenger.bus') as $id => $attr) {
                    self::register($container, MessengerInfra\ConsoleMessageReceiverBus::class, MessengerInfra\ConsoleMessageReceiverBus::class.'.'.$id)
                        ->setDecoratedService($id)
                        ->setArgument('$bus', MessengerInfra\ConsoleMessageReceiverBus::class.'.'.$id.'.inner');
                }
            }
        }

        if (null !== $aliasId) {
            self::alias($container, Message\DomainMessageBusInterface::class, $aliasId);
        }
    }
}
