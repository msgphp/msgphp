<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\Version as DoctrineOrmVersion;
use MsgPhp\Domain\Infra\DependencyInjection\Bundle\ContainerHelper;
use MsgPhp\Domain\Infra\Uuid\DomainId;
use MsgPhp\Eav\{AttributeIdInterface, AttributeValueIdInterface};
use MsgPhp\Eav\Entity\{Attribute, AttributeValue};
use MsgPhp\Eav\Infra\Doctrine\Repository\AttributeRepository;
use MsgPhp\Eav\Infra\Doctrine\Type\{AttributeIdType, AttributeValueIdType};
use Ramsey\Uuid\Uuid;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as BaseExtension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Extension extends BaseExtension implements PrependExtensionInterface
{
    public const ALIAS = 'msgphp_eav';

    public function getAlias(): string
    {
        return self::ALIAS;
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $bundles = ContainerHelper::getBundles($container);
        $classMapping = $config['class_mapping'];

        ContainerHelper::configureIdentityMap($container, $classMapping, [
            Attribute::class => 'id',
            AttributeValue::class => 'id',
        ]);
        ContainerHelper::configureEntityFactory($container, $classMapping, [
            Attribute::class => AttributeIdInterface::class,
            AttributeValue::class => AttributeValueIdInterface::class,
        ]);
        ContainerHelper::configureDoctrine($container);

        // persistence infra
        if (isset($bundles[DoctrineBundle::class])) {
            $this->prepareDoctrineBundle($config, $loader, $container);
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs = $container->getExtensionConfig($this->getAlias()), $container), $configs);
        $bundles = ContainerHelper::getBundles($container);
        $classMapping = $config['class_mapping'];

        if (isset($bundles[DoctrineBundle::class])) {
            if (class_exists(Uuid::class)) {
                $types = [];
                if (is_subclass_of($classMapping[AttributeIdInterface::class], DomainId::class)) {
                    $types[AttributeIdType::NAME] = AttributeIdType::class;
                }
                if (is_subclass_of($classMapping[AttributeValueIdInterface::class], DomainId::class)) {
                    $types[AttributeValueIdType::NAME] = AttributeValueIdType::class;
                }

                if ($types) {
                    $container->prependExtensionConfig('doctrine', [
                        'dbal' => [
                            'types' => $types,
                        ],
                    ]);
                }
            }

            if (class_exists(DoctrineOrmVersion::class)) {
                $container->prependExtensionConfig('doctrine', [
                    'orm' => [
                        'resolve_target_entities' => $classMapping,
                        'mappings' => [
                            'msgphp_eav' => [
                                'dir' => '%kernel.project_dir%/vendor/msgphp/eav/Infra/Doctrine/Resources/mapping',
                                'type' => 'xml',
                                'prefix' => 'MsgPhp\\Eav\\Entity',
                                'is_bundle' => false,
                            ],
                        ],
                    ],
                ]);
            }
        }
    }

    private function prepareDoctrineBundle(array $config, LoaderInterface $loader, ContainerBuilder $container): void
    {
        if (!class_exists(DoctrineOrmVersion::class)) {
            return;
        }

        $loader->load('doctrine.php');

        $classMapping = $config['class_mapping'];

        foreach ([
            AttributeRepository::class => $classMapping[Attribute::class],
        ] as $repository => $class) {
            if (null === $class) {
                $container->removeDefinition($repository);
            } else {
                $container->getDefinition($repository)->setArgument('$class', $class);
            }
        }
    }
}
