<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use Doctrine\ORM\Events as DoctrineOrmEvents;
use MsgPhp\Domain\{DomainIdentityHelper, DomainIdentityMappingInterface};
use MsgPhp\Domain\Factory\{DomainObjectFactory, DomainObjectFactoryInterface, EntityAwareFactory, EntityAwareFactoryInterface};
use MsgPhp\Domain\Infra\{Console as ConsoleInfra, Doctrine as DoctrineInfra, InMemory as InMemoryInfra, Messenger as MessengerInfra};
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
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

        self::initIdentityMapping($container);
        self::initObjectFactory($container);
        self::initMessageBus($container);

        if (FeatureDetection::isDoctrineOrmAvailable($container)) {
            self::initDoctrineOrm($container);
        }
        if (FeatureDetection::isConsoleAvailable($container)) {
            self::initConsole($container);
        }

        $container->addCompilerPass(new Compiler\ResolveDomainPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);

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
                ->setArgument('$classMapping', '%msgphp.domain.class_mapping%')
            ;

            $container->setAlias(DomainIdentityMappingInterface::class, new Alias(DoctrineInfra\DomainIdentityMapping::class, false));
        } else {
            $container->register(InMemoryInfra\DomainIdentityMapping::class)
                ->setPublic(false)
                ->setArgument('$mapping', '%msgphp.domain.identity_mapping%')
                ->setArgument('$accessor', $container->autowire(InMemoryInfra\ObjectFieldAccessor::class))
            ;

            $container->setAlias(DomainIdentityMappingInterface::class, new Alias(InMemoryInfra\DomainIdentityMapping::class, false));
        }

        $container->autowire(DomainIdentityHelper::class)
            ->setPublic(false)
        ;
    }

    private static function initObjectFactory(ContainerBuilder $container): void
    {
        $container->register(DomainObjectFactory::class)
            ->setPublic(false)
            ->setArgument('$classMapping', '%msgphp.domain.class_mapping%')
            ->addMethodCall('setNestedFactory', [new Reference(DomainObjectFactoryInterface::class)])
        ;

        $container->setAlias(DomainObjectFactoryInterface::class, new Alias(DomainObjectFactory::class, false));

        $container->autowire(EntityAwareFactory::class)
            ->setPublic(false)
            ->setDecoratedService(DomainObjectFactory::class)
            ->setArgument('$factory', new Reference(EntityAwareFactory::class.'.inner'))
            ->setArgument('$identifierMapping', '%msgphp.domain.id_class_mapping%')
        ;

        $container->setAlias(EntityAwareFactoryInterface::class, new Alias(DomainObjectFactoryInterface::class, false));

        if (FeatureDetection::isDoctrineOrmAvailable($container)) {
            $container->register(DoctrineInfra\EntityAwareFactory::class)
                ->setPublic(false)
                ->setDecoratedService(EntityAwareFactory::class)
                ->setArgument('$factory', new Reference(DoctrineInfra\EntityAwareFactory::class.'.inner'))
                ->setArgument('$em', new Reference('msgphp.doctrine.entity_manager'))
            ;
        }
    }

    private static function initMessageBus(ContainerBuilder $container): void
    {
        if (FeatureDetection::isMessengerAvailable($container)) {
            $container->setAlias('msgphp.messenger.command_bus', new Alias('message_bus', false));
            $container->setAlias('msgphp.messenger.event_bus', new Alias('message_bus', false));
            $container->register(MessengerInfra\DomainMessageBus::class)
                ->setPublic(false)
                ->setArgument('$commandBus', new Reference('msgphp.messenger.command_bus'))
                ->setArgument('$eventBus', new Reference('msgphp.messenger.event_bus'))
                ->setArgument('$eventClasses', '%msgphp.domain.event_classes%')
            ;
            $container->setAlias(DomainMessageBusInterface::class, new Alias(MessengerInfra\DomainMessageBus::class, false));
            $container->setAlias('msgphp.command_bus', new Alias('msgphp.messenger.command_bus', false));

            if (FeatureDetection::isConsoleAvailable($container)) {
                $container->autowire('msgphp.messenger.console_message_receiver', MessengerInfra\Middleware\ConsoleMessageReceiverMiddleware::class)
                    ->setPublic(false)
                ;
            }
        }
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

        $container->setAlias('msgphp.doctrine.entity_manager', new Alias('doctrine.orm.entity_manager', false));

        $container->register(DoctrineInfra\MappingConfig::class)
            ->setPublic(false)
            ->setArgument('$mappingFiles', '%msgphp.doctrine.mapping_files%')
            ->setArgument('$mappingConfig', '%msgphp.doctrine.mapping_config%')
        ;

        $container->register(DoctrineInfra\ObjectMappings::class)
            ->setPublic(false)
            ->addTag('msgphp.doctrine.object_mapping_provider')
        ;

        $container->autowire(DoctrineInfra\Event\ObjectMappingListener::class)
            ->setPublic(false)
            ->setArgument('$providers', new TaggedIteratorArgument('msgphp.doctrine.object_mapping_provider'))
            ->setArgument('$classMapping', '%msgphp.domain.class_mapping%')
            ->addTag('doctrine.event_listener', ['event' => DoctrineOrmEvents::loadClassMetadata])
        ;

        $container->registerForAutoconfiguration(DoctrineInfra\ObjectMappingProviderInterface::class)
            ->addTag('msgphp.doctrine.object_mapping_provider')
        ;

        if (FeatureDetection::hasFrameworkBundle($container)) {
            $container->autowire(DoctrineInfra\MappingCacheWarmer::class)
                ->setPublic(false)
                ->setArgument('$dirName', 'msgphp/doctrine-mapping')
                ->addTag('kernel.cache_warmer', ['priority' => 100])
            ;
        }
    }

    private static function initConsole(ContainerBuilder $container): void
    {
        $container->autowire(ConsoleInfra\Context\ClassContextFactory::class)
            ->setPublic(false)
            ->setAbstract(true)
            ->setArgument('$method', '__construct')
            ->setArgument('$classMapping', '%msgphp.domain.class_mapping%')
        ;

        $container->register(ConsoleInfra\Context\ClassContextElementFactory::class)
            ->setPublic(false)
        ;

        $container->setAlias(ConsoleInfra\Context\ClassContextElementFactoryInterface::class, new Alias(ConsoleInfra\Context\ClassContextElementFactory::class, false));

        $container->register(ConsoleInfra\MessageReceiver::class)
            ->setPublic(false)
            ->addTag('kernel.event_listener', ['event' => ConsoleEvents::COMMAND, 'method' => 'onCommand'])
            ->addTag('kernel.event_listener', ['event' => ConsoleEvents::TERMINATE, 'method' => 'onTerminate'])
        ;
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
