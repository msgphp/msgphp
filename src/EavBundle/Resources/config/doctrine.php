<?php

declare(strict_types=1);

use MsgPhp\Eav\Infra\Doctrine\Repository\AttributeRepository;
use MsgPhp\Eav\Repository\AttributeRepositoryInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
            ->autowire()
            ->private()
        ->load('MsgPhp\\Eav\\Infra\\Doctrine\\Repository\\', '%kernel.project_dir%/vendor/msgphp/eav/Infra/Doctrine/Repository')
        ->alias(AttributeRepositoryInterface::class, AttributeRepository::class)
    ;
};
