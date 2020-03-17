<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle;

use MsgPhp\Domain\Infrastructure\DependencyInjection\BundleHelper;
use MsgPhp\EavBundle\DependencyInjection\Compiler\CleanupPass;
use MsgPhp\EavBundle\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class MsgPhpEavBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CleanupPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);

        BundleHelper::build($container);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new Extension();
    }
}
