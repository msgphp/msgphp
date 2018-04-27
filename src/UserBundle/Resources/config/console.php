<?php

declare(strict_types=1);

use MsgPhp\Domain\Infra\Console;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
            ->private()
            ->bind(DomainMessageBusInterface::class, Console\DomainMessageBus::class)

        ->load('MsgPhp\\User\\Infra\\Console\\Command\\', '%kernel.project_dir%/vendor/msgphp/user/Infra/Console/Command')
    ;
};
