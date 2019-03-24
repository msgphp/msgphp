<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\DependencyInjection\Compiler;

use MsgPhp\Domain\Event\DomainEvent;
use MsgPhp\Domain\Infrastructure\DependencyInjection\ContainerHelper;
use MsgPhp\Domain\Infrastructure\DependencyInjection\FeatureDetection;
use MsgPhp\Domain\Message\DomainMessageBus;
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
        $container->setParameter($param = 'msgphp.domain.event_classes', array_merge(($container->hasParameter($param) ? $container->getParameter($param) : []), array_values(array_filter(array_map(function (string $file): string {
            return 'MsgPhp\\Domain\\Event\\'.basename($file, '.php');
        }, glob(\dirname(__DIR__, 3).'/Event/*Event.php')), function (string $class): bool {
            return !is_subclass_of($class, DomainEvent::class);
        }))));

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
        } else {
            foreach ($container->findTaggedServiceIds('msgphp.domain.message_aware') as $id => $attr) {
                $container->removeDefinition($id);
            }
        }

        $classMapping = $container->getParameter('msgphp.domain.class_mapping');
        foreach ($container->findTaggedServiceIds($tag = 'msgphp.domain.process_class_mapping') as $id => $attr) {
            $definition = $container->getDefinition($id);

            foreach ($attr as $attr) {
                if (!isset($attr['argument'])) {
                    continue;
                }

                $value = $definition->getArgument($attr['argument']);
                $definition->setArgument($attr['argument'], self::processClassMapping($value, $classMapping, !empty($attr['array_keys'])));
            }

            $definition->clearTag($tag);
        }

        if (FeatureDetection::isDoctrineOrmAvailable($container)) {
            $container->setParameter($param = 'msgphp.doctrine.mapping_config', ($container->hasParameter($param) ? $container->getParameter($param) : []) + [
                'mapping_dir' => '%kernel.project_dir%/config/msgphp/doctrine',
            ]);
        }
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private static function processClassMapping($value, array $classMapping, bool $arrayKeys = false)
    {
        if (\is_string($value) && isset($classMapping[$value])) {
            return $classMapping[$value];
        }

        if (!\is_array($value)) {
            return $value;
        }

        $result = [];

        foreach ($value as $k => $v) {
            $v = self::processClassMapping($v, $classMapping, $arrayKeys);
            if ($arrayKeys) {
                $k = self::processClassMapping($k, $classMapping);
            }

            $result[$k] = $v;
        }

        return $result;
    }
}
