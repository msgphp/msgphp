<?php

declare(strict_types=1);

use MsgPhp\EavBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
            ->autowire()
            ->private()

        ->load('MsgPhp\\Eav\\Infra\\Doctrine\\Repository\\', Configuration::getPackageDir().'/Infra/Doctrine/Repository/*Repository.php')
    ;
};
