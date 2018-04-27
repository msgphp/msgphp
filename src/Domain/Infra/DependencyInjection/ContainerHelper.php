<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\DBAL\Types\Type as DoctrineType;
use Doctrine\ORM\Version as DoctrineOrmVersion;
use MsgPhp\Domain\Infra\{Console as ConsoleInfra, SimpleBus as SimpleBusInfra};
use MsgPhp\Domain\Message\FallbackMessageHandler;
use Ramsey\Uuid\Doctrine as DoctrineUuid;
use SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle;
use SimpleBus\SymfonyBridge\SimpleBusEventBusBundle;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBusInterface;

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

    public static function registerConsoleClassContextFactory(ContainerBuilder $container, string $class, int $flags = 0): Definition
    {
        $definition = self::registerAnonymous($container, ConsoleInfra\Context\ClassContextFactory::class, true)
            ->setArgument('$class', $class)
            ->setArgument('$flags', $flags);

        if (class_exists(DoctrineOrmVersion::class) && self::hasBundle($container, DoctrineBundle::class)) {
            $definition = self::registerAnonymous($container, ConsoleInfra\Context\DoctrineEntityContextFactory::class)
                ->setAutowired(true)
                ->setArgument('$factory', $definition)
                ->setArgument('$class', $class);
        }

        return $definition;
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
            if (!isset($classMapping[$class])) {
                self::removeDefinitionWithAliases($container, $repository);
                continue;
            }

            $container->getDefinition($repository)
                ->setArgument('$class', $classMapping[$class]);
        }
    }

    public static function configureCommandMessages(ContainerBuilder $container, array $classMapping, array $commands): void
    {
        $messengerEnabled = interface_exists(MessageBusInterface::class);
        $simpleBusEnabled = self::hasBundle($container, SimpleBusCommandBusBundle::class);

        foreach ($container->findTaggedServiceIds($tag = 'msgphp.domain.command_handler') as $id => $attr) {
            $definition = $container->getDefinition($id);
            $command = self::getClassReflection($container, $definition->getClass() ?? $id)->getMethod('__invoke')->getParameters()[0]->getClass()->getName();

            if (empty($commands[$command])) {
                $container->removeDefinition($id);
                continue;
            }

            $mappedCommand = $classMapping[$command] ?? null;

            $definition->clearTag($tag);

            if ($messengerEnabled) {
                $definition->addTag('messenger.message_handler', ['handles' => $command]);
                if (null !== $mappedCommand) {
                    $definition->addTag('messenger.message_handler', ['handles' => $mappedCommand]);
                }
            }

            if ($simpleBusEnabled) {
                $definition
                    ->setPublic(true)
                    ->addTag('command_handler', ['handles' => $command]);
                if (null !== $mappedCommand) {
                    $definition->addTag('command_handler', ['handles' => $mappedCommand]);
                }
            }
        }
    }

    public static function configureEventMessages(ContainerBuilder $container, array $classMapping, array $events): void
    {
        $messengerHandler = $simpleBusHandler = null;
        if (interface_exists(MessageBusInterface::class)) {
            $messengerHandler = self::registerAnonymous($container, FallbackMessageHandler::class);
        }
        if (self::hasBundle($container, SimpleBusCommandBusBundle::class)) {
            $simpleBusHandler = self::registerAnonymous($container, FallbackMessageHandler::class);
            $simpleBusHandler->setPublic(true);
            if (self::hasBundle($container, SimpleBusEventBusBundle::class)) {
                $simpleBusHandler->setArgument('$bus', self::registerAnonymous($container, SimpleBusInfra\DomainMessageBus::class)
                    ->setArgument('$bus', new Reference('simple_bus.event_bus')));
            }
        }

        foreach ($events as $event) {
            $mappedEvent = $classMapping[$event] ?? null;

            if (null !== $messengerHandler) {
                $messengerHandler->addTag('messenger.message_handler', ['handles' => $event]);
                if (null !== $mappedEvent) {
                    $messengerHandler->addTag('messenger.message_handler', ['handles' => $mappedEvent]);
                }
            }

            if (null !== $simpleBusHandler) {
                $simpleBusHandler->addTag('command_handler', ['handles' => $event]);
                if (null !== $mappedEvent) {
                    $simpleBusHandler->addTag('command_handler', ['handles' => $mappedEvent]);
                }
            }
        }
    }

    private function __construct()
    {
    }
}
