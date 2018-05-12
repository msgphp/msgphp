<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use Doctrine\ORM\Events as DoctrineOrmEvents;
use MsgPhp\Domain\{DomainIdentityHelper, DomainIdentityMappingInterface};
use MsgPhp\Domain\Factory\{ClassMappingObjectFactory, DomainObjectFactory, DomainObjectFactoryInterface, EntityAwareFactory, EntityAwareFactoryInterface};
use MsgPhp\Domain\Infra\{Console as ConsoleInfra, Doctrine as DoctrineInfra, InMemory as InMemoryInfra, Messenger as MessengerInfra, SimpleBus as SimpleBusInfra};
use MsgPhp\Domain\Message\{DomainMessageBus, DomainMessageBusInterface};
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class BundleHelper
{
    private static $initialized = [];

    public static function build(ContainerBuilder $container): void
    {
        if ($initialized = &self::getInitialized($container, __FUNCTION__)) {
            return;
        }

        $container->addCompilerPass(new Compiler\ResolveDomainPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);

        self::initIdentityMapping($container);
        self::initObjectFactory($container);
        self::initMessageBus($container);

        if (FeatureDetection::isDoctrineOrmAvailable($container)) {
            self::initDoctrineOrm($container);
        }
        if (FeatureDetection::isConsoleAvailable($container)) {
            self::initConsole($container);
        }

        $initialized = true;
    }

    public static function boot(ContainerInterface $container): void
    {
        if ($initialized = &self::getInitialized($container, __FUNCTION__)) {
            return;
        }

        if ($container->hasParameter($param = 'msgphp.doctrine.type_config')) {
            foreach ($container->getParameter($param) as $config) {
                $config['type_class']::setClass($config['class']);
                $config['type_class']::setDataType($config['type']);
            }
        }

        $initialized = true;
    }

    private static function initIdentityMapping(ContainerBuilder $container): void
    {
        if (FeatureDetection::isDoctrineOrmAvailable($container)) {
            $container->register(DoctrineInfra\DomainIdentityMapping::class)
                ->setPublic(false)
                ->setArgument('$em', new Reference('msgphp.doctrine.entity_manager'))
                ->setArgument('$classMapping', '%msgphp.domain.class_mapping%');
            $container->setAlias(DomainIdentityMappingInterface::class, DoctrineInfra\DomainIdentityMapping::class);
        } else {
            $container->register($aliasId = InMemoryInfra\DomainIdentityMapping::class)
                ->setPublic(false)
                ->setArgument('$mapping', '%msgphp.domain.identity_mapping%')
                ->setArgument('$accessor', $container->autowire(InMemoryInfra\ObjectFieldAccessor::class));
            $container->setAlias(DomainIdentityMappingInterface::class, InMemoryInfra\DomainIdentityMapping::class);
        }

        $container->autowire(DomainIdentityHelper::class)
            ->setPublic(false);
    }

    private static function initObjectFactory(ContainerBuilder $container): void
    {
        $container->register(DomainObjectFactory::class)
            ->addMethodCall('setNestedFactory', [new Reference(DomainObjectFactoryInterface::class)]);
        $container->setAlias(DomainObjectFactoryInterface::class, DomainObjectFactory::class);

        $container->register(ClassMappingObjectFactory::class)
            ->setDecoratedService(DomainObjectFactory::class)
            ->setArgument('$factory', new Reference(ClassMappingObjectFactory::class.'.inner'))
            ->setArgument('$mapping', '%msgphp.domain.class_mapping%');

        $container->autowire(EntityAwareFactory::class)
            ->setArgument('$identifierMapping', '%msgphp.domain.id_class_mapping%');
        $container->setAlias(EntityAwareFactoryInterface::class, EntityAwareFactory::class);

        if (FeatureDetection::isDoctrineOrmAvailable($container)) {
            $container->register(DoctrineInfra\EntityAwareFactory::class)
                ->setDecoratedService(EntityAwareFactory::class)
                ->setArgument('$factory', new Reference(DoctrineInfra\EntityAwareFactory::class.'.inner'))
                ->setArgument('$em', new Reference('msgphp.doctrine.entity_manager'))
                ->setArgument('$classMapping', '%msgphp.domain.class_mapping%');
        }
    }

    private static function initMessageBus(ContainerBuilder $container): void
    {
        if (FeatureDetection::isMessengerAvailable($container)) {
            $container->setAlias('msgphp.messenger.bus', new Alias('message_bus', false));
            $container->setAlias('msgphp.messenger.event_bus', new Alias('msgphp.messenger.bus', false));

            $defaultBus = ContainerHelper::registerAnonymous($container, MessengerInfra\DomainMessageBus::class);
            $defaultBus->setArgument('$bus', new Reference('msgphp.messenger.bus'));
            $eventBus = ContainerHelper::registerAnonymous($container, MessengerInfra\DomainMessageBus::class);
            $eventBus->setArgument('$bus', new Reference('msgphp.messenger.event_bus'));

            if (FeatureDetection::isConsoleAvailable($container)) {
                $container->autowire('msgphp.messenger.console_message_receiver', MessengerInfra\Middleware\ConsoleMessageReceiverMiddleware::class);
            }
        } elseif (FeatureDetection::hasSimpleBusCommandBusBundle($container)) {
            $container->setAlias('msgphp.simple_bus.bus', 'simple_bus.command_bus');
            $container->setAlias('msgphp.simple_bus.event_bus', FeatureDetection::hasSimpleBusEventBusBundle($container) ? 'simple_bus.event_bus' : 'msgphp.simple_bus.bus');

            $defaultBus = ContainerHelper::registerAnonymous($container, SimpleBusInfra\DomainMessageBus::class);
            $defaultBus->setArgument('$bus', new Reference('msgphp.simple_bus.bus'));
            $eventBus = ContainerHelper::registerAnonymous($container, SimpleBusInfra\DomainMessageBus::class);
            $eventBus->setArgument('$bus', new Reference('msgphp.simple_bus.event_bus'));

            if (FeatureDetection::isConsoleAvailable($container)) {
                $container->autowire(SimpleBusInfra\Middleware\ConsoleMessageReceiverMiddleware::class)
                    ->setPublic(false)
                    ->addTag('command_bus_middleware')
                    ->addTag('event_bus_middleware');
            }
        } else {
            return;
        }

        $container->register(DomainMessageBus::class)
            ->setPublic(false)
            ->setArgument('$bus', $defaultBus)
            ->setArgument('$eventBus', $eventBus)
            ->setArgument('$eventClasses', '%msgphp.domain.events%');
        $container->setAlias(DomainMessageBusInterface::class, DomainMessageBus::class);
    }

    private static function initDoctrineOrm(ContainerBuilder $container): void
    {
        @mkdir($mappingDir = $container->getParameterBag()->resolveValue('%kernel.cache_dir%/msgphp/doctrine-mapping'), 0777, true);

        $container->prependExtensionConfig('doctrine', ['orm' => [
            'hydrators' => [
                DoctrineInfra\Hydration\ScalarHydrator::NAME => DoctrineInfra\Hydration\ScalarHydrator::class,
                DoctrineInfra\Hydration\SingleScalarHydrator::NAME => DoctrineInfra\Hydration\SingleScalarHydrator::class,
            ],
            'mappings' => [
                'msgphp' => [
                    'dir' => $mappingDir,
                    'type' => 'xml',
                    'prefix' => 'MsgPhp',
                    'is_bundle' => false,
                ],
            ],
        ]]);

        $container->addCompilerPass(new Compiler\DoctrineObjectFieldMappingPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 200);

        $container->setAlias('msgphp.doctrine.entity_manager', 'doctrine.orm.entity_manager');

        $container->register(DoctrineInfra\ObjectFieldMappings::class)
            ->setPublic(false)
            ->addTag('msgphp.doctrine.object_field_mappings', ['priority' => -100]);

        $container->register(DoctrineInfra\Event\ObjectFieldMappingListener::class)
            ->setPublic(false)
            ->addTag('msgphp.domain.process_class_mapping', ['argument' => '$mappings'])
            ->addTag('doctrine.event_listener', ['event' => DoctrineOrmEvents::loadClassMetadata]);

        if (FeatureDetection::hasFrameworkBundle($container)) {
            $container->register(DoctrineInfra\MappingCacheWarmer::class)
                ->setPublic(false)
                ->setArgument('$dirName', 'msgphp/doctrine-mapping')
                ->setArgument('$mappingFiles', '%msgphp.doctrine.mapping_files%')
                ->addTag('kernel.cache_warmer', ['priority' => 100]);
        }
    }

    private static function initConsole(ContainerBuilder $container): void
    {
        $container->autowire(ConsoleInfra\Context\ClassContextFactory::class)
            ->setPublic(false)
            ->setAbstract(true)
            ->setArgument('$method', '__construct')
            ->setArgument('$classMapping', '%msgphp.domain.class_mapping%');

        $container->register(ConsoleInfra\Context\ClassContextElementFactory::class)
            ->setPublic(false);
        $container->setAlias(ConsoleInfra\Context\ClassContextElementFactoryInterface::class, ConsoleInfra\Context\ClassContextElementFactory::class);

        $container->register(ConsoleInfra\MessageReceiver::class)
            ->setPublic(false)
            ->addTag('kernel.event_listener', ['event' => ConsoleEvents::COMMAND, 'method' => 'onCommand'])
            ->addTag('kernel.event_listener', ['event' => ConsoleEvents::TERMINATE, 'method' => 'onTerminate']);
    }

    private static function &getInitialized(ContainerInterface $container, string $key)
    {
        if (!isset(self::$initialized[$hash = spl_object_hash($container)."\0".$key])) {
            self::$initialized[$hash] = false;
        }

        return self::$initialized[$hash];
    }

    private function __construct()
    {
    }
}
