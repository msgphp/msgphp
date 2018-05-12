<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Compiler;

use MsgPhp\Domain\Infra\DependencyInjection\ContainerHelper;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
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
        $classMapping = $container->getParameter('msgphp.domain.class_mapping');
        foreach ($container->findTaggedServiceIds('msgphp.domain.process_class_mapping') as $id => $attr) {
            $definition = $container->getDefinition($id);

            foreach ($attr as $attr) {
                if (!isset($attr['argument'])) {
                    continue;
                }

                $value = $definition->getArgument($attr['argument']);
                $definition->setArgument($attr['argument'], self::processClassMapping($value, $classMapping, !empty($attr['array_keys'])));
            }

            $definition->clearTag('msgphp.domain.process_class_mapping');
        }

        if (!$container->has(DomainMessageBusInterface::class)) {
            foreach ($container->findTaggedServiceIds('msgphp.domain.message_aware') as $id => $attr) {
                ContainerHelper::removeId($container, $id);
            }
        }
    }

    private static function processClassMapping($value, array $classMapping, bool $arrayKeys = false)
    {
        if (is_string($value) && isset($classMapping[$value])) {
            return $classMapping[$value];
        }

        if (!is_array($value)) {
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
