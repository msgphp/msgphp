<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use Doctrine\DBAL\Types\Type as DoctrineType;
use Ramsey\Uuid\Doctrine as DoctrineUuid;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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

    public static function prepareDoctrineOrmRepositories(ContainerBuilder $container, array $classMapping, array $repositoryEntityMapping): void
    {
        foreach ($repositoryEntityMapping as $repository => $entity) {
            if (!isset($classMapping[$entity])) {
                $container->removeDefinition($repository);
                continue;
            }

            if (!$container->hasDefinition($repository)) {
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

    private function __construct()
    {
    }
}
