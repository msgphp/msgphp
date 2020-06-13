<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\EavBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $container): void {
    $service = function_exists('Symfony\Component\DependencyInjection\Loader\Configurator\service')
        ? 'Symfony\Component\DependencyInjection\Loader\Configurator\service'
        : 'Symfony\Component\DependencyInjection\Loader\Configurator\ref';

    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
            ->private()
            ->bind(EntityManagerInterface::class, $service('msgphp.doctrine.entity_manager'))
    ;

    foreach (Configuration::getPackageMetadata()->getDoctrineServicePrototypes() as $resource => $namespace) {
        $services->load($namespace, $resource);
    }
};
