<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use MsgPhp\Domain\Infra\{Console as ConsoleInfra};
use Doctrine\DBAL\Types\Type as DoctrineType;
use MsgPhp\Domain\Message\{MessageReceivingInterface, NoopMessageHandler};
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

        $container->setParameter($param = 'msgphp.domain.class_mapping', $container->hasParameter($param) ? $classMapping + $container->getParameter($param) : $classMapping);
        $container->setParameter($param = 'msgphp.domain.id_class_mapping', $container->hasParameter($param) ? $idClassMapping + $container->getParameter($param) : $idClassMapping);
        $container->setParameter($param = 'msgphp.domain.identity_mapping', $container->hasParameter($param) ? $identityMapping + $container->getParameter($param) : $identityMapping);
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

        $container->setParameter($param = 'msgphp.doctrine.type_config', $container->hasParameter($param) ? $typeConfig + $container->getParameter($param) : $typeConfig);
        $container->setParameter($param = 'msgphp.doctrine.mapping_files', $container->hasParameter($param) ? array_merge($container->getParameter($param), $mappingFiles) : $mappingFiles);

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
        foreach ($container->findTaggedServiceIds('msgphp.domain.command_handler') as $id => $attr) {
            $definition = $container->getDefinition($id);
            $command = (new \ReflectionMethod($definition->getClass() ?? $id, '__invoke'))->getParameters()[0]->getClass()->getName();

            if (empty($commands[$command])) {
                $container->removeDefinition($id);
                continue;
            }

            $handles = [$command];
            if (isset($classMapping[$command])) {
                $handles[] = $classMapping[$command];
            }

            ContainerHelper::tagMessageHandler($container, $definition, $handles);
        }
    }

    public static function prepareEventHandler(ContainerBuilder $container, array $classMapping, array $handles): void
    {
        foreach ($handles as $class) {
            if (isset($classMapping[$class])) {
                $handles[] = $classMapping[$class];
            }
        }

        ContainerHelper::tagMessageHandler($container, ContainerHelper::registerAnonymous($container, NoopMessageHandler::class), $handles);

        $container->setParameter($param = 'msgphp.domain.events', $container->hasParameter($param) ? array_merge($container->getParameter($param), $handles) : $handles);
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
