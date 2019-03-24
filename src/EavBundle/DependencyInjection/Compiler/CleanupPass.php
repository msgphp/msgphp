<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle\DependencyInjection\Compiler;

use MsgPhp\Domain\Infrastructure\DependencyInjection\ContainerHelper;
use MsgPhp\Eav\Command;
use MsgPhp\Eav\Repository;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class CleanupPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        ContainerHelper::removeIf($container, !$container->has(Repository\AttributeRepository::class), [
            Command\Handler\CreateAttributeHandler::class,
            Command\Handler\DeleteAttributeHandler::class,
        ]);
    }
}
