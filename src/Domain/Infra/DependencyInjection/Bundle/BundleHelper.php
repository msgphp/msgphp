<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Bundle;

use Symfony\Component\DependencyInjection\Container;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class BundleHelper
{
    public static function prepareDoctrineTypes(Container $container): void
    {
        if (!$container->hasParameter('msgphp.doctrine.type_config')) {
            return;
        }

        foreach ($container->getParameter('msgphp.doctrine.type_config') as $type => $config) {
            $type::setClass($config['class']);
            $type::setDataType($config['data_type']);
        }
    }

    private function __construct()
    {
    }
}
