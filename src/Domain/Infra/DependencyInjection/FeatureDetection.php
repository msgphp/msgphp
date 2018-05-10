<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\Version as DoctrineOrmVersion;
use MsgPhp\EavBundle\MsgPhpEavBundle;
use SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle;
use SimpleBus\SymfonyBridge\SimpleBusEventBusBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class FeatureDetection
{
    public static function hasFrameworkBundle(ContainerInterface $container): bool
    {
        return ContainerHelper::hasBundle($container, FrameworkBundle::class);
    }

    public static function hasSecurityBundle(ContainerInterface $container): bool
    {
        return ContainerHelper::hasBundle($container, SecurityBundle::class);
    }

    public static function hasTwigBundle(ContainerInterface $container): bool
    {
        return ContainerHelper::hasBundle($container, TwigBundle::class);
    }

    public static function hasDoctrineBundle(ContainerInterface $container): bool
    {
        return ContainerHelper::hasBundle($container, DoctrineBundle::class);
    }

    public static function hasSimpleBusCommandBusBundle(ContainerInterface $container): bool
    {
        return ContainerHelper::hasBundle($container, SimpleBusCommandBusBundle::class);
    }

    public static function hasSimpleBusEventBusBundle(ContainerInterface $container): bool
    {
        return ContainerHelper::hasBundle($container, SimpleBusEventBusBundle::class);
    }

    public static function hasMsgPhpEavBundle(ContainerInterface $container): bool
    {
        return ContainerHelper::hasBundle($container, MsgPhpEavBundle::class);
    }

    public static function isFormAvailable(ContainerInterface $container): bool
    {
        return self::hasFrameworkBundle($container) && interface_exists(FormInterface::class);
    }

    public static function isValidatorAvailable(ContainerInterface $container): bool
    {
        return self::hasFrameworkBundle($container) && interface_exists(ValidatorInterface::class);
    }

    public static function isConsoleAvailable(ContainerInterface $container): bool
    {
        return self::hasFrameworkBundle($container) && class_exists(ConsoleEvents::class);
    }

    public static function isMessengerAvailable(ContainerInterface $container): bool
    {
        return self::hasFrameworkBundle($container) && interface_exists(MessageBusInterface::class);
    }

    public static function isDoctrineOrmAvailable(ContainerInterface $container): bool
    {
        return self::hasDoctrineBundle($container) && class_exists(DoctrineOrmVersion::class);
    }

    private function __construct()
    {
    }
}
