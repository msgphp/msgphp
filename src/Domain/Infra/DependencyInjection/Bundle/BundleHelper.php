<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Bundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\Events as DoctrineOrmEvents;
use Doctrine\ORM\Version as DoctrineOrmVersion;
use MsgPhp\Domain\Infra\DependencyInjection\Compiler;
use MsgPhp\Domain\Infra\Doctrine as DoctrineInfra;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class BundleHelper
{
    public static function initDomain(ContainerBuilder $container): void
    {
        ContainerHelper::addCompilerPassOnce($container, Compiler\ResolveDomainPass::class);

        if (class_exists(DoctrineOrmVersion::class)) {
            ContainerHelper::addCompilerPassOnce($container, Compiler\DoctrineObjectFieldMappingPass::class);

            $container->setParameter('msgphp.doctrine.mapping_cache_dirname', 'msgphp/doctrine-mapping');

            $container->register(DoctrineInfra\Event\ObjectFieldMappingListener::class)
                ->setPublic(false)
                ->setArgument('$typeConfig', '%msgphp.doctrine.type_config%')
                ->addTag('doctrine.event_listener', ['event' => DoctrineOrmEvents::loadClassMetadata]);

            if (ContainerHelper::hasBundle($container, DoctrineBundle::class)) {
                @mkdir($mappingDir = $container->getParameterBag()->resolveValue('%kernel.cache_dir%/%msgphp.doctrine.mapping_cache_dirname%'), 0777, true);

                $container->prependExtensionConfig('doctrine', ['orm' => ['mappings' => ['msgphp' => [
                    'dir' => $mappingDir,
                    'type' => 'xml',
                    'prefix' => 'MsgPhp',
                    'is_bundle' => false,
                ]]]]);
            }
        }
    }

    public static function initDoctrineTypes(Container $container): void
    {
        static $prepared = false;

        if ($prepared || !$container->hasParameter($param = 'msgphp.doctrine.type_config')) {
            return;
        }

        foreach ($container->getParameter($param) as $config) {
            $config['type']::setClass($config['class']);
            $config['type']::setDataType($config['data_type']);
        }

        $prepared = true;
    }

    private function __construct()
    {
    }
}
