<?php

declare(strict_types=1);

namespace MsgPhp\UserBundle;

use MsgPhp\Domain\Infra\DependencyInjection\BundleHelper;
use MsgPhp\UserBundle\DependencyInjection\Compiler\CleanupPass;
use MsgPhp\UserBundle\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class MsgPhpUserBundle extends Bundle
{
    public function boot(): void
    {
        BundleHelper::boot($this->container);
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CleanupPass());

        BundleHelper::build($container);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new Extension();
    }
}
