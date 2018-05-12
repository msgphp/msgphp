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
            ->private()

        ->load('MsgPhp\\Eav\\Infra\\Doctrine\\Repository\\', Configuration::getPackageDir().'/Infra/Doctrine/Repository/*Repository.php')
            ->bind(EntityManagerInterface::class, ref('msgphp.doctrine.entity_manager'))

        ->set(Doctrine\ObjectFieldMappings::class)
            ->tag('msgphp.doctrine.object_field_mappings', ['priority' => -100])
    ;
};
