<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\Version as DoctrineOrmVersion;
use MsgPhp\Domain\Infra\{Console as ConsoleInfra, SimpleBus as SimpleBusInfra};
use MsgPhp\Domain\Message\FallbackMessageHandler;
use SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle;
use SimpleBus\SymfonyBridge\SimpleBusEventBusBundle;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

    public static function hasBundle(ContainerInterface $container, string $class): bool
    {
        return in_array($class, $container->getParameter('kernel.bundles'), true);
    }

    public static function getBundles(ContainerInterface $container): array
    {
        return array_flip($container->getParameter('kernel.bundles'));
    }

    public static function getClassReflection(ContainerBuilder $container, ?string $class): \ReflectionClass
    {
        if (!$class || !($reflection = $container->getReflectionClass($class))) {
            throw new InvalidArgumentException(sprintf('Invalid class "%s".', $class));
        }

        return $reflection;
    }

    public static function removeId(ContainerBuilder $container, string $id): void
    {
        $container->removeDefinition($id);
        $container->removeAlias($id);

        foreach ($container->getAliases() as $aliasId => $alias) {
            if ($id === (string) $alias) {
                $container->removeAlias($aliasId);
            }
        }
    }

    public static function removeIf(ContainerBuilder $container, bool $condition, array $ids): void
    {
        if (!$condition) {
            return;
        }

        foreach ($ids as $id) {
            self::removeId($container, $id);
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

    public static function configureCommandMessages(ContainerBuilder $container, array $classMapping, array $commands): void
    {
        $messengerEnabled = interface_exists(MessageBusInterface::class);
        $simpleBusEnabled = self::hasBundle($container, SimpleBusCommandBusBundle::class);

        foreach ($container->findTaggedServiceIds('msgphp.domain.command_handler') as $id => $attr) {
            $definition = $container->getDefinition($id);
            $command = self::getClassReflection($container, $definition->getClass() ?? $id)->getMethod('__invoke')->getParameters()[0]->getClass()->getName();

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
