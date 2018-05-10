<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ExtensionHelper
{
    public static function configureIdentityMapping(ContainerBuilder $container, array $classMapping, array $identityMapping): void
    {
        foreach ($identityMapping as $class => $mapping) {
            if (isset($classMapping[$class]) && !isset($identityMapping[$classMapping[$class]])) {
                $identityMapping[$classMapping[$class]] = $mapping;
            }
        }

        $values = $container->hasParameter($param = 'msgphp.domain.identity_mapping') ? $container->getParameter($param) : [];
        $values[] = $identityMapping;

        $container->setParameter($param, $values);
    }

    public static function configureEntityFactory(ContainerBuilder $container, array $classMapping, array $idClassMapping): void
    {
        foreach ($idClassMapping as $class => $idClass) {
            if (isset($classMapping[$class]) && !isset($idClassMapping[$classMapping[$class]])) {
                $idClassMapping[$classMapping[$class]] = $idClass;
            }
        }

        $values = $container->hasParameter($param = 'msgphp.domain.class_mapping') ? $container->getParameter($param) : [];
        $values[] = $classMapping;

        $container->setParameter($param, $values);

        $values = $container->hasParameter($param = 'msgphp.domain.id_class_mapping') ? $container->getParameter($param) : [];
        $values[] = $idClassMapping;

        $container->setParameter($param, $values);
    }

    private function __construct()
    {
    }
}
