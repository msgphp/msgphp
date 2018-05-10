<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use Doctrine\ORM\Events as DoctrineOrmEvents;
use MsgPhp\Domain\DomainIdentityHelper;
use MsgPhp\Domain\Infra\{Console as ConsoleInfra, Doctrine as DoctrineInfra, SimpleBus as SimpleBusInfra};
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

        $container->register(DomainIdentityHelper::class)
            ->setPublic(false)
            ->setAutowired(true);

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

        $container->setAlias('msgphp.doctrine.entity_manager', new Alias('doctrine.orm.entity_manager', false));

        $container->register(DoctrineInfra\ObjectFieldMappings::class)
            ->setPublic(false)
            ->addTag('msgphp.doctrine.object_field_mappings', ['priority' => -100]);

        $container->register(DoctrineInfra\Event\ObjectFieldMappingListener::class)
            ->setPublic(false)
            ->addTag('msgphp.domain.process_class_mapping', ['argument' => '$mappings'])
            ->addTag('doctrine.event_listener', ['event' => DoctrineOrmEvents::loadClassMetadata]);
    }

    private static function initConsole(ContainerBuilder $container): void
    {
        $container->register(ConsoleInfra\Context\ClassContextFactory::class)
            ->setPublic(false)
            ->setAbstract(true)
            ->setAutowired(true)
            ->setArgument('$method', '__construct')
            ->setArgument('$classMapping', '%msgphp.domain.class_mapping%');

        $container->register(ConsoleInfra\Context\ClassContextElementFactory::class)
            ->setPublic(false);
        $container->setAlias(ConsoleInfra\Context\ClassContextElementFactoryInterface::class, new Alias(ConsoleInfra\Context\ClassContextElementFactory::class, false));

        $container->register(ConsoleInfra\MessageReceiver::class)
            ->setPublic(false)
            ->addTag('kernel.event_listener', ['event' => ConsoleEvents::COMMAND, 'method' => 'onCommand'])
            ->addTag('kernel.event_listener', ['event' => ConsoleEvents::TERMINATE, 'method' => 'onTerminate']);

        if (FeatureDetection::hasSimpleBusCommandBusBundle($container)) {
            $container->register(SimpleBusInfra\Middleware\ConsoleMessageReceiverMiddleware::class)
                ->setPublic(false)
                ->setAutowired(true)
                ->addTag('command_bus_middleware');
        }
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
