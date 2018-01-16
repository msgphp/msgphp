<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle;

use MsgPhp\Domain\Infra\DependencyInjection\Bundle\BundleHelper;
use MsgPhp\EavBundle\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class MsgPhpEavBundle extends Bundle
{
    public function boot(): void
    {
        BundleHelper::initDoctrineTypes($this->container);
    }

    public function build(ContainerBuilder $container): void
    {
        BundleHelper::initDomain($container);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new Extension();
    }
}
