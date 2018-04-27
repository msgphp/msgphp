<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\Events as DoctrineOrmEvents;
use Doctrine\ORM\Version as DoctrineOrmVersion;
use MsgPhp\Domain\Infra\{Console as ConsoleInfra, Doctrine as DoctrineInfra, SimpleBus as SimpleBusInfra};
use SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class BundleHelper
{
    private static $initialized = [];

    public static function initDomain(ContainerBuilder $container): void
    {
        if ($initialized = &self::getInitialized($container, __FUNCTION__)) {
            return;
        }

        $container->addCompilerPass(new Compiler\ResolveDomainPass());

        self::initConsole($container);
        self::initDoctrineOrm($container);

        $initialized = true;
    }

    public static function initDoctrineTypes(Container $container): void
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

    private static function initConsole(ContainerBuilder $container): void
    {
        if (!class_exists(ConsoleEvents::class)) {
            return;
        }

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

        if (ContainerHelper::hasBundle($container, SimpleBusCommandBusBundle::class)) {
            $container->register(SimpleBusInfra\Middleware\ConsoleMessageReceiverMiddleware::class)
                ->setPublic(false)
                ->setAutowired(true)
                ->addTag('command_bus_middleware');
        }
    }

    private static function initDoctrineOrm(ContainerBuilder $container): void
    {
        if (!class_exists(DoctrineOrmVersion::class)) {
            return;
        }

        $container->addCompilerPass(new Compiler\DoctrineObjectFieldMappingPass());

        $container->register(DoctrineInfra\Event\ObjectFieldMappingListener::class)
            ->setPublic(false)
            ->setArgument('$mapping', [])
            ->addTag('doctrine.event_listener', ['event' => DoctrineOrmEvents::loadClassMetadata]);

        if (ContainerHelper::hasBundle($container, DoctrineBundle::class)) {
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
        }
    }

    private static function &getInitialized(Container $container, string $key)
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
