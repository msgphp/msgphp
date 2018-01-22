<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use MsgPhp\Domain\Infra\DependencyInjection\Bundle\{ConfigHelper, ContainerHelper};
use MsgPhp\Eav\{AttributeIdInterface, AttributeValueIdInterface, Entity};
use MsgPhp\Eav\Infra\Doctrine as DoctrineInfra;
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

        ConfigHelper::resolveResolveDataTypeMapping($container, $config['data_type_mapping']);
        ConfigHelper::resolveClassMapping(Configuration::DATA_TYPE_MAP, $config['data_type_mapping'], $config['class_mapping']);

        ContainerHelper::configureIdentityMap($container, $config['class_mapping'], Configuration::IDENTITY_MAP);
        ContainerHelper::configureEntityFactory($container, $config['class_mapping'], Configuration::AGGREGATE_ROOTS);
        ContainerHelper::configureDoctrineOrmMapping($container, self::getDoctrineMappingFiles($config, $container), [DoctrineInfra\EntityFieldsMapping::class]);

        $bundles = ContainerHelper::getBundles($container);

        // persistence infra
        if (isset($bundles[DoctrineBundle::class])) {
            $this->prepareDoctrineBundle($config, $loader, $container);
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs = $container->getExtensionConfig($this->getAlias()), $container), $configs);

        ConfigHelper::resolveResolveDataTypeMapping($container, $config['data_type_mapping']);
        ConfigHelper::resolveClassMapping(Configuration::DATA_TYPE_MAP, $config['data_type_mapping'], $config['class_mapping']);

        ContainerHelper::configureDoctrineTypes($container, $config['data_type_mapping'], $config['class_mapping'], [
            AttributeIdInterface::class => DoctrineInfra\Type\AttributeIdType::class,
            AttributeValueIdInterface::class => DoctrineInfra\Type\AttributeValueIdType::class,
        ]);
        ContainerHelper::configureDoctrineOrmTargetEntities($container, $config['class_mapping']);
    }

    private function prepareDoctrineBundle(array $config, LoaderInterface $loader, ContainerBuilder $container): void
    {
        if (!ContainerHelper::isDoctrineOrmEnabled($container)) {
            return;
        }

        $loader->load('doctrine.php');

        $classMapping = $config['class_mapping'];

        foreach ([
            DoctrineInfra\Repository\AttributeRepository::class => $classMapping[Entity\Attribute::class],
        ] as $repository => $class) {
            if (null === $class) {
                ContainerHelper::removeDefinitionWithAliases($container, $repository);
                continue;
            }

            $container->getDefinition($repository)
                ->setArgument('$class', $class);
        }
    }

    private static function getDoctrineMappingFiles(array $config, ContainerBuilder $container): array
    {
        return glob(dirname((new \ReflectionClass(AttributeIdInterface::class))->getFileName()).'/Infra/Doctrine/Resources/dist-mapping/*.orm.xml');
    }
}
