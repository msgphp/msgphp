<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection\Bundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\DBAL\Types\Type as DoctrineType;
use Doctrine\ORM\Version as DoctrineOrmVersion;
use Ramsey\Uuid\Doctrine as DoctrineUuid;
use SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle;
use MsgPhp\Domain\Infra\SimpleBus as SimpleBusInfra;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ContainerHelper
{
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

    public static function addCompilerPassOnce(ContainerBuilder $container, string $class, callable $initializer = null, $type = PassConfig::TYPE_BEFORE_OPTIMIZATION, int $priority = 0): void
    {
        $passes = array_flip(array_map(function (CompilerPassInterface $pass): string {
            return get_class($pass);
        }, $container->getCompiler()->getPassConfig()->getPasses()));

        if (!isset($passes[$class])) {
            $container->addCompilerPass(null === $initializer ? new $class() : $initializer(), $type, $priority);
        }
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

    public static function configureIdentityMapping(ContainerBuilder $container, array $classMapping, array $identityMapping): void
    {
        foreach ($identityMapping as $class => $mapping) {
            if (isset($classMapping[$class])) {
                $identityMapping[$classMapping[$class]] = $mapping;
            }
        }

        $values = $container->hasParameter($param = 'msgphp.domain.identity_mapping') ? $container->getParameter($param) : [];
        $values[] = $identityMapping;

        $container->setParameter($param, $values);
    }

    public static function configureEntityFactory(ContainerBuilder $container, array $classMapping, array $idClassMapping): void
    {
        $values = $container->hasParameter($param = 'msgphp.domain.class_mapping') ? $container->getParameter($param) : [];
        $values[] = $classMapping;

        $container->setParameter($param, $values);

        $values = $container->hasParameter($param = 'msgphp.domain.id_class_mapping') ? $container->getParameter($param) : [];
        $values[] = $idClassMapping;

        $container->setParameter($param, $values);
    }

    public static function configureDoctrineTypes(ContainerBuilder $container, array $dataTypeMapping, array $classMapping, array $typeMapping): void
    {
        if (!class_exists(DoctrineType::class)) {
            return;
        }

        $types = $mappingTypes = $typeConfig = [];
        $uuidMapping = [
            'uuid' => DoctrineUuid\UuidType::class,
            'uuid_binary' => DoctrineUuid\UuidBinaryType::class,
            'uuid_binary_ordered_time' => DoctrineUuid\UuidBinaryOrderedTimeType::class,
        ];

        foreach ($typeMapping as $class => $type) {
            $dataType = $dataTypeMapping[$class] ?? DoctrineType::INTEGER;

            if (isset($uuidMapping[$dataType])) {
                if (!class_exists($uuidClass = $uuidMapping[$dataType])) {
                    throw new \LogicException(sprintf('Data type "%s" for identifier "%s" requires "ramsey/uuid-doctrine".', $dataType, $class));
                }

                $types[$uuidClass::NAME] = $uuidClass;

                if ('uuid_binary' === $dataType || 'uuid_binary_ordered_time' === $dataType) {
                    $mappingTypes[$dataType] = 'binary';
                }
            }

            if (!defined($type.'::NAME')) {
                throw new \LogicException(sprintf('Type class "%s" for identifier "%s" requires a "NAME" constant.', $type, $class));
            }

            $types[$type::NAME] = $type;
            $typeConfig[$type::NAME] = ['class' => $classMapping[$class] ?? $class, 'type' => $type, 'data_type' => $dataType];
        }

        $config = $types ? ['types' => $types] : [];
        if ($mappingTypes) {
            $config['mapping_types'] = $mappingTypes;
        }

        if ($config) {
            if ($container->hasParameter($param = 'msgphp.doctrine.type_config')) {
                $typeConfig += $container->getParameter($param);
            }

            $container->setParameter($param, $typeConfig);

            if (self::hasBundle($container, DoctrineBundle::class)) {
                $container->prependExtensionConfig('doctrine', [
                    'dbal' => $config,
                ]);
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

    public static function removeDisabledCommandMessages(ContainerBuilder $container, array $commands): void
    {
        if (!self::hasBundle($container, SimpleBusCommandBusBundle::class)) {
            return;
        }

        foreach ($container->findTaggedServiceIds('command_handler') as $id => $attr) {
            foreach ($attr as $attr) {
                if (!isset($attr['handles']) || !empty($commands[$attr['handles']])) {
                    continue;
                }

                $container->removeDefinition($id);
            }
        }
    }

    public static function registerEventMessages(ContainerBuilder $container, array $events): void
    {
        if (!self::hasBundle($container, SimpleBusCommandBusBundle::class)) {
            return;
        }

        $definition = $container->register(uniqid('msgphp'), SimpleBusInfra\EventMessageHandler::class);
        $definition
            ->setPublic(true)
            ->setArgument('$bus', new Reference('simple_bus.event_bus', ContainerBuilder::NULL_ON_INVALID_REFERENCE));

        foreach ($events as $event) {
            $definition->addTag('command_handler', ['handles' => $event]);
        }
    }

    private function __construct()
    {
    }
}
