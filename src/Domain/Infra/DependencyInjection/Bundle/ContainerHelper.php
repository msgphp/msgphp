<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Bundle;

use Doctrine\ORM\Events as DoctrineOrmEvents;
use MsgPhp\Domain\{CommandBusInterface, EventBusInterface};
use MsgPhp\Domain\Entity\{ChainEntityFactory, ClassMappingEntityFactory, EntityFactoryInterface};
use MsgPhp\Domain\Infra\Doctrine\Mapping\ObjectFieldMappingListener;
use MsgPhp\Domain\Infra\SimpleBus\{DomainCommandBus, DomainEventBus};
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ContainerHelper
{
    public static function getBundles(ContainerBuilder $container): array
    {
        return array_flip($container->getParameter('kernel.bundles'));
    }

    public static function getClassReflector(ContainerBuilder $container): \Closure
    {
        return function (string $class) use ($container): \ReflectionClass {
            if (null === $reflection = $container->getReflectionClass($class)) {
                throw new InvalidArgumentException(sprintf('Invalid class "%s".', $class));
            }

            return $reflection;
        };
    }

    public static function addCompilerPassOnce(ContainerBuilder $container, string $class, callable $initializer = null, $type = PassConfig::TYPE_BEFORE_OPTIMIZATION, int $priority = 0): void
    {
        $passes = array_flip(array_map(function (CompilerPassInterface $pass): string {
            return get_class($pass);
        }, $container->getCompiler()->getPassConfig()->getPasses()));

        if (!isset($passes[$class])) {
            $container->addCompilerPass(null === $initializer ? new $class() : $initializer(), $type, $priority);
        }
    }

    public static function configureEntityFactory(ContainerBuilder $container, array $mapping, array $idMapping): void
    {
        if (!$container->hasDefinition('msgphp.entity_factory')) {
            $container->register('msgphp.entity_factory', ChainEntityFactory::class)
                ->setPublic(false)
                ->setArgument('$factories', new TaggedIteratorArgument('msgphp.entity_factory'));
        }

        if (!$container->has(EntityFactoryInterface::class)) {
            $container->setAlias(EntityFactoryInterface::class, new Alias('msgphp.entity_factory', false));
        }

        $container->register('msgphp.entity_factory.'.md5(uniqid()), ClassMappingEntityFactory::class)
            ->setPublic(false)
            ->setArgument('$mapping', $mapping)
            ->setArgument('$idMapping', $idMapping)
            ->setArgument('$factory', new Reference('msgphp.entity_factory'))
            ->addTag('msgphp.entity_factory');
    }

    public static function configureDoctrineObjectFieldMapping(ContainerBuilder $container, string $class): void
    {
        if (!class_exists(DoctrineOrmEvents::class)) {
            return;
        }

        if (!$container->has(ObjectFieldMappingListener::class)) {
            $container->register(ObjectFieldMappingListener::class)
                ->setPublic(false)
                ->addTag('doctrine.event_listener', ['event' => DoctrineOrmEvents::loadClassMetadata]);
        }

        if (!$container->has($class)) {
            $container->register($class)
                ->setPublic(false)
                ->addTag('msgphp.doctrine.object_field_mapping');
        }
    }

    public static function configureSimpleCommandBus(ContainerBuilder $container): void
    {
        if (!$container->has(DomainCommandBus::class)) {
            $container->register(DomainCommandBus::class)
                ->setPublic(false)
                ->addArgument(new Reference('command_bus'));
        }

        if (!$container->has(CommandBusInterface::class)) {
            $container->setAlias(CommandBusInterface::class, new Alias(DomainCommandBus::class, false));
        }
    }

    public static function configureSimpleEventBus(ContainerBuilder $container): void
    {
        if (!$container->has(DomainEventBus::class)) {
            $container->register(DomainEventBus::class)
                ->setPublic(false)
                ->addArgument(new Reference('event_bus'));
        }

        if (!$container->has(EventBusInterface::class)) {
            $container->setAlias(EventBusInterface::class, new Alias(DomainEventBus::class, false));
        }
    }

    private function __construct()
    {
    }
}
