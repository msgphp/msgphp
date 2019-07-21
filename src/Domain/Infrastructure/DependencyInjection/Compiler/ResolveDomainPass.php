<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\DependencyInjection\Compiler;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Infrastructure\DependencyInjection\ContainerHelper;
use MsgPhp\Domain\Infrastructure\DependencyInjection\FeatureDetection;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ResolveDomainPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->setParameter($param = 'msgphp.domain.event_classes', array_merge(($container->hasParameter($param) ? $container->getParameter($param) : []), array_values(array_map(static function (string $file): string {
            return 'MsgPhp\\Domain\\Projection\\Event\\'.basename($file, '.php');
        }, glob(\dirname(__DIR__, 3).'/Projection/Event/*.php')))));

        if ($container->has(DomainMessageBus::class)) {
            $commandBus = $container->findDefinition($alias = 'msgphp.command_bus');
            $commandBusId = null;
            $container->removeAlias($alias);

            foreach ($container->getDefinitions() as $id => $definition) {
                if ($definition === $commandBus) {
                    $commandBusId = (string) $id;
                    break;
                }
            }

            foreach ($container->findTaggedServiceIds('msgphp.domain.command_handler') as $id => $attr) {
                ContainerHelper::tagCommandHandler($container, $id, $commandBusId, $attr[0]['handles'] ?? null);
            }
            ContainerHelper::clearTag($container, 'msgphp.domain.message_aware');
        } else {
            foreach ($container->findTaggedServiceIds('msgphp.domain.message_aware') as $id => $attr) {
                $container->removeDefinition($id);
            }
        }

        if (FeatureDetection::isDoctrineOrmAvailable($container)) {
            $container->setParameter($param = 'msgphp.doctrine.mapping_config', ($container->hasParameter($param) ? $container->getParameter($param) : []) + [
                'mapping_dir' => '%kernel.project_dir%/config/msgphp/doctrine',
            ]);
        }
    }
}
