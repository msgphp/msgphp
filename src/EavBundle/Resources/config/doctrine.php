<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Eav\Infra\Doctrine;
use MsgPhp\EavBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
            ->private()

        ->load('MsgPhp\\Eav\\Infra\\Doctrine\\Repository\\', Configuration::getPackageGlob().'/Infra/Doctrine/Repository/*Repository.php')
            ->bind(EntityManagerInterface::class, ref('msgphp.doctrine.entity_manager'))

        ->set(Doctrine\ObjectMappings::class)
    ;
};
