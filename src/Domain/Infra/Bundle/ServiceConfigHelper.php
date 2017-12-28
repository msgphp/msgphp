<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Bundle;

use MsgPhp\Domain\{CommandBusInterface, EventBusInterface};
use MsgPhp\Domain\Entity\{ChainEntityFactory, ClassMappingEntityFactory, EntityFactoryInterface};
use MsgPhp\Domain\Infra\SimpleBus\{DomainCommandBus, DomainEventBus};
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ServiceConfigHelper
{
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
