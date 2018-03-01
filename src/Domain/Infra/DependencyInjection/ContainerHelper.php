<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\DBAL\Types\Type as DoctrineType;
use Doctrine\ORM\Version as DoctrineOrmVersion;
use Ramsey\Uuid\Doctrine as DoctrineUuid;
use SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle;
use MsgPhp\Domain\Infra\SimpleBus as SimpleBusInfra;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ContainerHelper
{
    private static $counter = 0;

    public static function hasBundle(Container $container, string $class): bool
    {
        return in_array($class, $container->getParameter('kernel.bundles'), true);
    }

    public static function getBundles(Container $container): array
    {
        return array_flip($container->getParameter('kernel.bundles'));
    }

    public static function getClassReflector(ContainerBuilder $container): \Closure
    {
        return function (string $class) use ($container): \ReflectionClass {
            return self::getClassReflection($container, $class);
        };
    }

    public static function getClassReflection(ContainerBuilder $container, ?string $class): \ReflectionClass
    {
        if (!$class || !($reflection = $container->getReflectionClass($class))) {
            throw new InvalidArgumentException(sprintf('Invalid class "%s".', $class));
        }

        return $reflection;
    }

    public static function removeDefinitionWithAliases(ContainerBuilder $container, string $id): void
    {
        $container->removeDefinition($id);

        foreach ($container->getAliases() as $aliasId => $alias) {
            if ($id === (string) $alias) {
                $container->removeAlias($aliasId);
            }
        }
    }

    public static function removeIf(ContainerBuilder $container, $condition, array $ids): void
    {
        if (!$condition) {
            return;
        }

        foreach ($ids as $id) {
            self::removeDefinitionWithAliases($container, $id);
            $container->removeAlias($id);
        }
    }

    public static function registerAnonymous(ContainerBuilder $container, string $class, bool $child = false): Definition
    {
        $definition = $child ? new ChildDefinition($class) : new Definition($class);
        $definition->setPublic(false);

        return $container->setDefinition($class.'.'.ContainerBuilder::hash(__METHOD__.++self::$counter), $definition);
    }

    public static function configureIdentityMapping(ContainerBuilder $container, array $classMapping, array $identityMapping): void
    {
        foreach ($identityMapping as $class => $mapping) {
            if (isset($classMapping[$class]) && !isset($identityMapping[$classMapping[$class]])) {
                $identityMapping[$classMapping[$class]] = $mapping;
            }
        }

        $values = $container->hasParameter($param = 'msgphp.domain.identity_mapping') ? $container->getParameter($param) : [];
        $values[] = $identityMapping;

        $container->setParameter($param, $values);
    }

    public static function configureEntityFactory(ContainerBuilder $container, array $classMapping, array $idClassMapping): void
    {
        foreach ($idClassMapping as $class => $idClass) {
            if (isset($classMapping[$class]) && !isset($idClassMapping[$classMapping[$class]])) {
                $idClassMapping[$classMapping[$class]] = $idClass;
            }
        }

        $values = $container->hasParameter($param = 'msgphp.domain.class_mapping') ? $container->getParameter($param) : [];
        $values[] = $classMapping;

        $container->setParameter($param, $values);

        $values = $container->hasParameter($param = 'msgphp.domain.id_class_mapping') ? $container->getParameter($param) : [];
        $values[] = $idClassMapping;

        $container->setParameter($param, $values);
    }

    public static function configureDoctrineTypes(ContainerBuilder $container, array $classMapping, array $idTypeMapping, array $typeClassMapping): void
    {
        if (!class_exists(DoctrineType::class)) {
            return;
        }

        $dbalTypes = $mappingTypes = $typeConfig = [];
        $uuidMapping = [
            'uuid' => DoctrineUuid\UuidType::class,
            'uuid_binary' => DoctrineUuid\UuidBinaryType::class,
            'uuid_binary_ordered_time' => DoctrineUuid\UuidBinaryOrderedTimeType::class,
        ];

        foreach ($typeClassMapping as $idClass => $typeClass) {
            $type = $idTypeMapping[$idClass] ?? DoctrineType::INTEGER;

            if (isset($uuidMapping[$type])) {
                if (!class_exists($uuidClass = $uuidMapping[$type])) {
                    throw new \LogicException(sprintf('Type "%s" for identifier "%s" requires "ramsey/uuid-doctrine".', $type, $idClass));
                }

                $dbalTypes[$uuidClass::NAME] = $uuidClass;

                if ('uuid_binary' === $type || 'uuid_binary_ordered_time' === $type) {
                    $mappingTypes[$type] = 'binary';
                }
            }

            if (!defined($typeClass.'::NAME')) {
                throw new \LogicException(sprintf('Type class "%s" for identifier "%s" requires a "NAME" constant.', $typeClass, $idClass));
            }

            $dbalTypes[$typeClass::NAME] = $typeClass;
            $typeConfig[$typeClass::NAME] = ['class' => $classMapping[$idClass] ?? $idClass, 'type' => $type, 'type_class' => $typeClass];
        }

        if ($dbalTypes || $mappingTypes) {
            if ($container->hasParameter($param = 'msgphp.doctrine.type_config')) {
                $typeConfig += $container->getParameter($param);
            }

            $container->setParameter($param, $typeConfig);

            if (self::hasBundle($container, DoctrineBundle::class)) {
                $container->prependExtensionConfig('doctrine', ['dbal' => ['types' => $dbalTypes, 'mapping_types' => $mappingTypes]]);
            }
        }
    }

    public static function configureDoctrineOrmMapping(ContainerBuilder $container, array $mappingFiles, array $objectFieldMappings = []): void
    {
        if (!class_exists(DoctrineOrmVersion::class)) {
            return;
        }

        $values = $container->hasParameter($param = 'msgphp.doctrine.mapping_files') ? $container->getParameter($param) : [];
        $values[] = $mappingFiles;

        $container->setParameter($param, $values);

        foreach ($objectFieldMappings as $class) {
            $container->register($class)
                ->setPublic(false)
                ->addTag('msgphp.doctrine.object_field_mapping', ['priority' => -100]);
        }
    }

    public static function configureDoctrineOrmTargetEntities(ContainerBuilder $container, array $classMapping): void
    {
        if (!class_exists(DoctrineOrmVersion::class) || !self::hasBundle($container, DoctrineBundle::class)) {
            return;
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'resolve_target_entities' => $classMapping,
            ],
        ]);
    }

    public static function configureDoctrineOrmRepositories(ContainerBuilder $container, array $classMapping, array $repositoryMapping): void
    {
        if (!class_exists(DoctrineOrmVersion::class)) {
            return;
        }

        foreach ($repositoryMapping as $repository => $class) {
            if (null === $class || !isset($classMapping[$class])) {
                self::removeDefinitionWithAliases($container, $repository);
                continue;
            }

            $container->getDefinition($repository)
                ->setArgument('$class', $classMapping[$class]);
        }
    }

    public static function configureCommandMessages(ContainerBuilder $container, array $classMapping, array $commands): void
    {
        if (!self::hasBundle($container, SimpleBusCommandBusBundle::class)) {
            return;
        }

        foreach ($container->findTaggedServiceIds($tag = 'command_handler') as $id => $attr) {
            foreach ($attr as $attr) {
                if (!isset($attr[$attrName = 'handles'])) {
                    continue;
                }

                if ($commands[$command = $attr[$attrName]] ?? false) {
                    if (isset($classMapping[$command])) {
                        $container->getDefinition($id)
                            ->addTag($tag, [$attrName => $classMapping[$command]]);
                    }

                    continue;
                }

                $container->removeDefinition($id);
            }
        }
    }

    public static function configureEventMessages(ContainerBuilder $container, array $classMapping, array $events): void
    {
        if (!self::hasBundle($container, SimpleBusCommandBusBundle::class)) {
            return;
        }

        $definition = self::registerAnonymous($container, SimpleBusInfra\EventMessageHandler::class);
        $definition
            ->setPublic(true)
            ->setArgument('$bus', new Reference('simple_bus.event_bus', ContainerBuilder::NULL_ON_INVALID_REFERENCE));

        if (class_exists(ConsoleEvents::class)) {
            $definition
                ->addTag('kernel.event_listener', ['event' => ConsoleEvents::COMMAND, 'method' => 'onConsoleCommand'])
                ->addTag('kernel.event_listener', ['event' => ConsoleEvents::TERMINATE, 'method' => 'onConsoleTerminate']);
        }

        foreach ($events as $event) {
            $definition->addTag($tag = 'command_handler', [$attrName = 'handles' => $event]);

            if (isset($classMapping[$event])) {
                $definition->addTag($tag, [$attrName => $classMapping[$event]]);
            }
        }
    }

    private function __construct()
    {
    }
}
