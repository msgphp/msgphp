<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Doctrine\Test;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain as LegacyMappingDriverChain;
use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator as LegacySymfonyFileLocator;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;
use MsgPhp\Domain\Infrastructure\Doctrine\MappingConfig;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EntityManagerTestTrait
{
    /** @var EntityManagerInterface */
    private static $em;

    /**
     * @beforeClass
     */
    public static function initEm(): void
    {
        self::destroyEm();

        foreach (self::getEntityIdTypes() as $type => $class) {
            $type::setClass($class);

            if (Type::hasType($type::NAME)) {
                Type::overrideType($type::NAME, $type);
            } else {
                Type::addType($type::NAME, $type);
            }
        }

        $driver = class_exists(MappingDriverChain::class)
            ? new MappingDriverChain()
            : new LegacyMappingDriverChain();

        foreach (self::getEntityMappings() as $type => $paths) {
            foreach ($paths as $ns => $path) {
                switch ($type) {
                    case 'xml':
                        $fileLocator = class_exists(SymfonyFileLocator::class)
                            ? new SymfonyFileLocator([$path => $ns], '.orm.xml')
                            : new LegacySymfonyFileLocator([$path => $ns], '.orm.xml');
                        $driver->addDriver(new XmlDriver($fileLocator), $ns);

                        break;
                    case 'annot':
                        $driver->addDriver(new AnnotationDriver(new AnnotationReader(), $path), $ns);

                        break;
                    default:
                        throw new \LogicException('Unknown driver type.');
                }
            }
        }

        $config = new Configuration();
        $config->setMetadataDriverImpl($driver);
        $config->setProxyDir(sys_get_temp_dir().'/msgphp_'.md5(static::class).'/proxy');
        $config->setProxyNamespace(static::class);

        self::$em = EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $config);

        $targets = new ResolveTargetEntityListener();
        foreach (self::getClassMapping() as $class => $mappedClass) {
            $targets->addResolveTargetEntity($class, $mappedClass, []);
        }
        self::$em->getEventManager()->addEventListener(Events::loadClassMetadata, $targets);
    }

    /**
     * @afterClass
     */
    public static function destroyEm(): void
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if (null !== self::$em) {
            self::cleanEm();
            self::$em->close();
            self::$em = null;
        }

        if (is_dir($dir = sys_get_temp_dir().'/msgphp_'.md5(static::class))) {
            /** @var \SplFileInfo $file */
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath() ?: $file->getPathname());
                } else {
                    unlink($file->getRealPath() ?: $file->getPathname());
                }
            }

            rmdir($dir);
        }
    }

    /**
     * @before
     */
    public static function prepareEm(): void
    {
        if (!self::$em->isOpen()) {
            self::$em = EntityManager::create(self::$em->getConnection(), self::$em->getConfiguration(), self::$em->getEventManager());
        }

        self::cleanEm();

        if (self::createSchema()) {
            (new SchemaTool(self::$em))->createSchema(self::$em->getMetadataFactory()->getAllMetadata());
        }
    }

    /**
     * @after
     */
    public static function cleanEm(): void
    {
        self::$em->clear();

        if (self::createSchema()) {
            (new SchemaTool(self::$em))->dropSchema(self::$em->getMetadataFactory()->getAllMetadata());
        }
    }

    abstract protected static function getClassMapping(): array;

    abstract protected static function createSchema(): bool;

    abstract protected static function getEntityMappings(): iterable;

    abstract protected static function getEntityIdTypes(): iterable;

    private static function createEntityDistMapping(string $source): string
    {
        $files = [];
        mkdir($target = sys_get_temp_dir().'/msgphp_'.md5(static::class).'/mapping/'.md5($source), 0777, true);

        /** @var \SplFileInfo $file */
        foreach (new \DirectoryIterator($source) as $file) {
            if ('xml' === $file->getExtension()) {
                $files[] = $file->getPathname();
            }
        }

        $config = new MappingConfig($files, ['key_max_length' => 255]);

        foreach ($config->mappingFiles as $file) {
            file_put_contents($target.'/'.basename($file), $config->interpolate((string) file_get_contents($file)));
        }

        return $target;
    }
}
