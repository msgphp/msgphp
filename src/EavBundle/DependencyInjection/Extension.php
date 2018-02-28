<?php

declare(strict_types=1);

namespace MsgPhp\EavBundle\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\Version as DoctrineOrmVersion;
use MsgPhp\Domain\Infra\DependencyInjection\ContainerHelper;
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

        ContainerHelper::configureIdentityMapping($container, $config['class_mapping'], Configuration::IDENTITY_MAPPING);
        ContainerHelper::configureEntityFactory($container, $config['class_mapping'], Configuration::AGGREGATE_ROOTS);
        ContainerHelper::configureDoctrineOrmMapping($container, self::getDoctrineMappingFiles($config, $container), [DoctrineInfra\EntityFieldsMapping::class]);

        // persistence infra
        if (class_exists(DoctrineOrmVersion::class) && ContainerHelper::hasBundle($container, DoctrineBundle::class)) {
            $this->prepareDoctrineOrm($config, $loader, $container);
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs = $container->getExtensionConfig($this->getAlias()), $container), $configs);

        ContainerHelper::configureDoctrineTypes($container, $config['class_mapping'], $config['id_type_mapping'], [
            AttributeIdInterface::class => DoctrineInfra\Type\AttributeIdType::class,
            AttributeValueIdInterface::class => DoctrineInfra\Type\AttributeValueIdType::class,
        ]);
        ContainerHelper::configureDoctrineOrmTargetEntities($container, $config['class_mapping']);
    }

    private function prepareDoctrineOrm(array $config, LoaderInterface $loader, ContainerBuilder $container): void
    {
        $loader->load('doctrine.php');

        ContainerHelper::configureDoctrineOrmRepositories($container, $config['class_mapping'], [
            DoctrineInfra\Repository\AttributeRepository::class => Entity\Attribute::class,
        ]);
    }

    private static function getDoctrineMappingFiles(array $config, ContainerBuilder $container): array
    {
        $baseDir = dirname(ContainerHelper::getClassReflection($container, AttributeIdInterface::class)->getFileName()).'/Infra/Doctrine/Resources/dist-mapping';

        return glob($baseDir.'/*.orm.xml');
    }
}
