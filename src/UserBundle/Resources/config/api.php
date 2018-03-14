<?php

declare(strict_types=1);

use MsgPhp\User\Infra\Api;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->private()

        ->set(Api\Security\ApiSecurityUserProvider::class)
    ;
};
