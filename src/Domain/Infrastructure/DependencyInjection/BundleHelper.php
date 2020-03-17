<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\DependencyInjection;

use Doctrine\ORM\Events as DoctrineOrmEvents;
use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Factory\GenericDomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Console as ConsoleInfrastructure;
use MsgPhp\Domain\Infrastructure\Doctrine as DoctrineInfrastructure;
use MsgPhp\Domain\Infrastructure\Messenger as MessengerInfrastructure;
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
    /** @var array<string, bool> */
    private static $initialized = [];

    public static function build(ContainerBuilder $container): void
    {
        if ($container->isCompiled() || $initialized = &self::getInitialized($container, __FUNCTION__)) {
            return;
        }

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

    private static function initObjectFactory(ContainerBuilder $container): void
    {
        $container->register(GenericDomainObjectFactory::class)
            ->setPublic(false)
            ->setArgument('$classMapping', '%msgphp.domain.class_mapping%')
            ->addMethodCall('setNestedFactory', [new Reference(DomainObjectFactory::class)])
        ;

        $container->setAlias(DomainObjectFactory::class, new Alias(GenericDomainObjectFactory::class, false));

        if (FeatureDetection::isDoctrineOrmAvailable($container)) {
            $container->register(DoctrineInfrastructure\DomainObjectFactory::class)
                ->setPublic(false)
                ->setDecoratedService(GenericDomainObjectFactory::class)
                ->setArgument('$factory', new Reference(DoctrineInfrastructure\DomainObjectFactory::class.'.inner'))
                ->setArgument('$em', new Reference('msgphp.doctrine.entity_manager'))
            ;
        }
    }

    private static function initMessageBus(ContainerBuilder $container): void
    {
        if (FeatureDetection::isMessengerAvailable($container)) {
            $container->setAlias('msgphp.messenger.command_bus', new Alias('messenger.default_bus', false));
            $container->setAlias('msgphp.messenger.event_bus', new Alias('messenger.default_bus', false));
            $container->setAlias('msgphp.command_bus', new Alias('msgphp.messenger.command_bus', false));
            $container->register(MessengerInfrastructure\DomainMessageBus::class)
                ->setPublic(false)
                ->setArgument('$commandBus', new Reference('msgphp.messenger.command_bus'))
                ->setArgument('$eventBus', new Reference('msgphp.messenger.event_bus'))
                ->setArgument('$eventClasses', '%msgphp.domain.event_classes%')
            ;
            $container->setAlias(DomainMessageBus::class, new Alias(MessengerInfrastructure\DomainMessageBus::class, false));
        }
    }

    private static function initDoctrineOrm(ContainerBuilder $container): void
    {
        @mkdir($mappingDir = $container->getParameterBag()->resolveValue('%kernel.cache_dir%/msgphp/doctrine-mapping'), 0777, true);

        $container->prependExtensionConfig('doctrine', ['orm' => [
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

        $container->register(DoctrineInfrastructure\MappingConfig::class)
            ->setPublic(false)
            ->setArgument('$mappingFiles', '%msgphp.doctrine.mapping_files%')
            ->setArgument('$mappingConfig', '%msgphp.doctrine.mapping_config%')
        ;

        $container->register(DoctrineInfrastructure\DomainObjectMappings::class)
            ->setPublic(false)
            ->addTag('msgphp.doctrine.object_mapping_provider')
        ;

        $container->autowire(DoctrineInfrastructure\Event\ObjectMappingListener::class)
            ->setPublic(false)
            ->setArgument('$providers', new TaggedIteratorArgument('msgphp.doctrine.object_mapping_provider'))
            ->setArgument('$classMapping', '%msgphp.domain.class_mapping%')
            ->addTag('doctrine.event_listener', ['event' => DoctrineOrmEvents::loadClassMetadata])
        ;

        $container->registerForAutoconfiguration(DoctrineInfrastructure\ObjectMappingProvider::class)
            ->addTag('msgphp.doctrine.object_mapping_provider')
        ;

        if (FeatureDetection::hasFrameworkBundle($container)) {
            $container->autowire(DoctrineInfrastructure\MappingCacheWarmer::class)
                ->setPublic(false)
                ->setArgument('$dirName', 'msgphp/doctrine-mapping')
                ->addTag('kernel.cache_warmer', ['priority' => 100])
            ;
        }
    }

    private static function initConsole(ContainerBuilder $container): void
    {
        $container->autowire(ConsoleInfrastructure\Definition\ClassContextDefinition::class)
            ->setPublic(false)
            ->setAbstract(true)
            ->setArgument('$method', '__construct')
            ->setArgument('$classMapping', '%msgphp.domain.class_mapping%')
        ;
    }

    private static function &getInitialized(ContainerInterface $container, string $key): bool
    {
        if (!isset(self::$initialized[$hash = spl_object_hash($container)."\0".$key])) {
            self::$initialized[$hash] = false;
        }

        return self::$initialized[$hash];
    }
}
