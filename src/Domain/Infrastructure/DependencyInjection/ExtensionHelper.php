<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\DependencyInjection;

use MsgPhp\Domain\Infrastructure\Console as ConsoleInfrastructure;
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
    public static function configureDomain(ContainerBuilder $container, array $classMapping): void
    {
        $container->setParameter($param = 'msgphp.domain.class_mapping', $container->hasParameter($param) ? $classMapping + $container->getParameter($param) : $classMapping);
    }

    public static function configureDoctrineOrm(ContainerBuilder $container, array $classMapping, array $mappingFiles): void
    {
        $container->setParameter($param = 'msgphp.doctrine.mapping_files', $container->hasParameter($param) ? array_merge($container->getParameter($param), $mappingFiles) : $mappingFiles);

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'resolve_target_entities' => $classMapping,
            ],
        ]);
    }

    public static function finalizeCommandHandlers(ContainerBuilder $container, array $classMapping, array $commands, array $events): void
    {
        foreach ($container->findTaggedServiceIds($tag = 'msgphp.domain.command_handler') as $id => $attr) {
            $definition = $container->getDefinition($id);
            $param = (new \ReflectionMethod($definition->getClass() ?? (string) $id, '__invoke'))->getParameters()[0] ?? null;

            if (null === $param || null === $command = $param->getClass()) {
                throw new \LogicException('Missing command class type-hint for handler service "'.$id.'".');
            }

            $command = $command->getName();
            $enabled = $commands[$command] ?? true;

            if (!$enabled) {
                $container->removeDefinition($id);

                continue;
            }

            $definition
                ->clearTag($tag)
                ->addTag('msgphp.domain.message_aware')
                ->addTag($tag, ['handles' => $classMapping[$command] ?? $command])
            ;
        }

        foreach ($events as $i => $class) {
            $events[$i] = $classMapping[$class] ?? $class;
        }

        $container->setParameter($param = 'msgphp.domain.event_classes', $container->hasParameter($param) ? array_merge($container->getParameter($param), $events) : $events);
    }

    public static function finalizeDoctrineOrmRepositories(ContainerBuilder $container, array $classMapping, array $entityRepositoryMapping): void
    {
        foreach ($entityRepositoryMapping as $entity => $repository) {
            if (!$container->hasDefinition($repository)) {
                continue;
            }

            if (!isset($classMapping[$entity])) {
                $container->removeDefinition($repository);

                continue;
            }

            ($definition = $container->getDefinition($repository))
                ->setArgument('$class', $classMapping[$entity])
                ;

            foreach (class_implements($definition->getClass() ?? $repository) as $interface) {
                $container->setAlias($interface, new Alias($repository, false));
            }
        }
    }

    public static function finalizeConsoleCommands(ContainerBuilder $container, array $commands, array $consoleDomainCommandsMapping): void
    {
        foreach ($consoleDomainCommandsMapping as $domainCommand => $consoleCommands) {
            foreach ($consoleCommands as $consoleCommand) {
                if (!$container->hasDefinition($consoleCommand)) {
                    continue;
                }

                if (empty($commands[$domainCommand])) {
                    $container->removeDefinition($consoleCommand);

                    continue;
                }

                $container->getDefinition($consoleCommand)
                    ->addTag('msgphp.domain.message_aware')
                ;
            }
        }
    }

    public static function registerConsoleClassContextDefinition(ContainerBuilder $container, string $class, int $flags = 0): Definition
    {
        $definition = ContainerHelper::registerAnonymous($container, ConsoleInfrastructure\Definition\ClassContextDefinition::class, true)
            ->setArgument('$class', $class)
            ->setArgument('$flags', $flags)
        ;

        if (FeatureDetection::isDoctrineOrmAvailable($container)) {
            $definition = ContainerHelper::registerAnonymous($container, ConsoleInfrastructure\Definition\DoctrineContextDefinition::class)
                ->setArgument('$definition', $definition)
                ->setArgument('$em', new Reference('msgphp.doctrine.entity_manager'))
                ->setArgument('$class', $class)
            ;
        }

        return $definition;
    }
}
