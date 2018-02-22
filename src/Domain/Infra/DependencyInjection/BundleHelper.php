<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\Events as DoctrineOrmEvents;
use Doctrine\ORM\Version as DoctrineOrmVersion;
use MsgPhp\Domain\Infra\{Console as ConsoleInfra, Doctrine as DoctrineInfra};
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

        $container->registerForAutoconfiguration(ConsoleInfra\ContextBuilder\ContextElementProviderInterface::class)
            ->addTag('msgphp.console.context_element_provider');

        if (class_exists(DoctrineOrmVersion::class)) {
            $container->addCompilerPass(new Compiler\DoctrineObjectFieldMappingPass());

            $container->setParameter('msgphp.doctrine.mapping_cache_dirname', 'msgphp/doctrine-mapping');

            $container->register(DoctrineInfra\Event\ObjectFieldMappingListener::class)
                ->setPublic(false)
                ->addTag('doctrine.event_listener', ['event' => DoctrineOrmEvents::loadClassMetadata]);

            if (ContainerHelper::hasBundle($container, DoctrineBundle::class)) {
                @mkdir($mappingDir = $container->getParameterBag()->resolveValue('%kernel.cache_dir%/%msgphp.doctrine.mapping_cache_dirname%'), 0777, true);

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
