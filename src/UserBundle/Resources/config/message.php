<?php

declare(strict_types=1);

use MsgPhp\Domain\Infra\DependencyInjection\ContainerHelper;
use MsgPhp\User\UserIdInterface;
use SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Messenger\MessageBusInterface;

/** @var ContainerBuilder $container */
$container = $container ?? (function (): ContainerBuilder { throw new \LogicException('Invalid context.'); })();
$reflector = ContainerHelper::getClassReflector($container);
$simpleCommandBusEnabled = ContainerHelper::hasBundle($container, SimpleBusCommandBusBundle::class);

return function (ContainerConfigurator $container) use ($reflector, $simpleCommandBusEnabled): void {
    $baseDir = dirname($reflector(UserIdInterface::class)->getFileName());
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->public()

        ->load($ns = 'MsgPhp\\User\\Command\\Handler\\', $handlers = $baseDir.'/Command/Handler/*Handler.php')
    ;

    $messengerEnabled = interface_exists(MessageBusInterface::class);
    foreach (glob($handlers) as $file) {
        $service = $services->get($handler = $ns.basename($file, '.php'));
        $handles = $reflector($handler)->getMethod('__invoke')->getParameters()[0]->getClass()->getName();

        if ($messengerEnabled) {
            $service->tag('messenger.message_handler', ['handles' => $handles]);
        }

        if ($simpleCommandBusEnabled) {
            $service->tag('command_handler', ['handles' => $handles]);
        }
    }
};
