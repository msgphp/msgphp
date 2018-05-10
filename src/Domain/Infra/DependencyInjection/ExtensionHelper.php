<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use MsgPhp\Domain\Infra\{Console as ConsoleInfra, SimpleBus as SimpleBusInfra};
use Doctrine\DBAL\Types\Type as DoctrineType;
use MsgPhp\Domain\Message\{FallbackMessageHandler, MessageReceivingInterface};
use Ramsey\Uuid\Doctrine as DoctrineUuid;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ExtensionHelper
{
    public static function configureDomain(ContainerBuilder $container, array $classMapping, array $idClassMapping, array $identityMapping): void
    {
        foreach ($idClassMapping as $class => $idClass) {
            if (isset($classMapping[$class]) && !isset($idClassMapping[$classMapping[$class]])) {
                $idClassMapping[$classMapping[$class]] = $idClass;
            }
        }
        foreach ($identityMapping as $class => $mapping) {
            if (isset($classMapping[$class]) && !isset($identityMapping[$classMapping[$class]])) {
                $identityMapping[$classMapping[$class]] = $mapping;
            }
        }

        $values = $container->hasParameter($param = 'msgphp.domain.class_mapping') ? $container->getParameter($param) : [];
        $values[] = $classMapping;
        $container->setParameter($param, $values);

        $values = $container->hasParameter($param = 'msgphp.domain.id_class_mapping') ? $container->getParameter($param) : [];
        $values[] = $idClassMapping;
        $container->setParameter($param, $values);

        $values = $container->hasParameter($param = 'msgphp.domain.identity_mapping') ? $container->getParameter($param) : [];
        $values[] = $identityMapping;
        $container->setParameter($param, $values);
    }

    public static function configureDoctrineOrm(ContainerBuilder $container, array $classMapping, array $idTypeMapping, array $typeClassMapping, array $mappingFiles): void
    {
        $dbalTypes = $dbalMappingTypes = $typeConfig = [];
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
                    $dbalMappingTypes[$type] = 'binary';
                }
            }

            if (!defined($typeClass.'::NAME')) {
                throw new \LogicException(sprintf('Type class "%s" for identifier "%s" requires a "NAME" constant.', $typeClass, $idClass));
            }

            $dbalTypes[$typeClass::NAME] = $typeClass;
            $typeConfig[$typeClass::NAME] = ['class' => $classMapping[$idClass] ?? $idClass, 'type' => $type, 'type_class' => $typeClass];
        }

        $typeConfigValues = $container->hasParameter($param = 'msgphp.doctrine.type_config') ? $container->getParameter($param) : [];
        $typeConfigValues += $typeConfig;
        $container->setParameter($param, $typeConfigValues);

        $mappingFileValues = $container->hasParameter($param = 'msgphp.doctrine.mapping_files') ? $container->getParameter($param) : [];
        $mappingFileValues[] = $mappingFiles;
        $container->setParameter($param, $mappingFileValues);

        $container->prependExtensionConfig('doctrine', [
            'dbal' => [
                'types' => $dbalTypes,
                'mapping_types' => $dbalMappingTypes,
            ],
            'orm' => [
                'resolve_target_entities' => $classMapping,
            ],
        ]);
    }

    public static function prepareCommandHandlers(ContainerBuilder $container, array $classMapping, array $commands): void
    {
        $messengerEnabled = FeatureDetection::isMessengerAvailable($container);
        $simpleBusEnabled = FeatureDetection::hasSimpleBusCommandBusBundle($container);

        foreach ($container->findTaggedServiceIds('msgphp.domain.command_handler') as $id => $attr) {
            $definition = $container->getDefinition($id);
            $command = (new \ReflectionMethod($definition->getClass() ?? $id, '__invoke'))->getParameters()[0]->getClass()->getName();

            if (empty($commands[$command])) {
                $container->removeDefinition($id);
                continue;
            }

            $mappedCommand = $classMapping[$command] ?? null;

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

            $definition->addTag('msgphp.domain.message_aware');
        }
    }

    public static function prepareEventHandler(ContainerBuilder $container, array $classMapping, array $events): void
    {
        $messengerHandler = $simpleBusHandler = null;
        if (FeatureDetection::isMessengerAvailable($container)) {
            $messengerHandler = ContainerHelper::registerAnonymous($container, FallbackMessageHandler::class);
        }
        if (FeatureDetection::hasSimpleBusCommandBusBundle($container)) {
            $simpleBusHandler = ContainerHelper::registerAnonymous($container, FallbackMessageHandler::class);
            $simpleBusHandler->setPublic(true);
            if (FeatureDetection::hasSimpleBusEventBusBundle($container)) {
                $simpleBusHandler->setArgument('$bus', ContainerHelper::registerAnonymous($container, SimpleBusInfra\DomainMessageBus::class)
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

    public static function prepareConsoleCommands(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('msgphp.domain.console_command') as $id => $attr) {
            $definition = $container->getDefinition($id);

            if (is_subclass_of($definition->getClass() ?? $id, MessageReceivingInterface::class)) {
                $definition->addTag('msgphp.domain.message_aware');
            }
        }
    }

    public static function prepareDoctrineOrmRepositories(ContainerBuilder $container, array $classMapping, array $repositoryEntityMapping): void
    {
        foreach ($repositoryEntityMapping as $repository => $entity) {
            if (!isset($classMapping[$entity])) {
                $container->removeDefinition($repository);
                continue;
            }

            ($definition = $container->getDefinition($repository))
                ->setArgument('$class', $classMapping[$entity]);

            foreach (class_implements($definition->getClass() ?? $repository) as $interface) {
                if (!$container->has($interface)) {
                    $container->setAlias($interface, new Alias($repository, false));
                }
            }
        }
    }

    public static function registerConsoleClassContextFactory(ContainerBuilder $container, string $class, int $flags = 0): Definition
    {
        $definition = ContainerHelper::registerAnonymous($container, ConsoleInfra\Context\ClassContextFactory::class, true)
            ->setArgument('$class', $class)
            ->setArgument('$flags', $flags);

        if (FeatureDetection::isDoctrineOrmAvailable($container)) {
            $definition = ContainerHelper::registerAnonymous($container, ConsoleInfra\Context\DoctrineEntityContextFactory::class)
                ->setArgument('$factory', $definition)
                ->setArgument('$em', new Reference('msgphp.doctrine.entity_manager'))
                ->setArgument('$class', $class);
        }

        return $definition;
    }

    private function __construct()
    {
    }
}
