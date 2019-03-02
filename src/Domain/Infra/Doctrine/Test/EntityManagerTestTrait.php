<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine\Test;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use MsgPhp\Domain\Infra\Doctrine\MappingConfig;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
trait EntityManagerTestTrait
{
    /**
     * @var EntityManagerInterface
     */
    private static $em;

    private static function getEmCacheDir(): string
    {
        static $dir;

        return $dir ?? $dir = sys_get_temp_dir().'/msgphp_'.md5(static::class);
    }

    private static function initEm(): void
    {
        self::cleanEmCache();

        foreach (self::getEntityIdTypes() as $type => $class) {
            $type::setClass($class);

            if (Type::hasType($type::NAME)) {
                Type::overrideType($type::NAME, $type);
            } else {
                Type::addType($type::NAME, $type);
            }
        }

        $driver = new MappingDriverChain();
        foreach (self::getEntityMappings() as $type => $paths) {
            foreach ($paths as $ns => $path) {
                switch ($type) {
                    case 'xml':
                        $driver->addDriver(new XmlDriver($paths, '.orm.xml'), $ns);
                        break;
                    case 'annot':
                        $driver->addDriver(new AnnotationDriver(new AnnotationReader(), $paths), $ns);
                        break;
                    default:
                        throw new \LogicException('Unknown driver type.');
                }
            }
        }

        $config = new Configuration();
        $config->setMetadataDriverImpl($driver);
        $config->setProxyDir(self::getEmCacheDir().'/proxy');
        $config->setProxyNamespace(static::class);

        self::$em = EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $config);
    }

    private static function destroyEm(): void
    {
        (new SchemaTool(self::$em))->dropDatabase();

        self::$em->close();
        self::$em = null;

        self::cleanEmCache();
    }

    private static function prepareEm(): void
    {
        if (!self::$em->isOpen()) {
            self::$em = EntityManager::create(self::$em->getConnection(), self::$em->getConfiguration(), self::$em->getEventManager());
        }

        if (self::createSchema()) {
            $schemaTool = new SchemaTool(self::$em);
            $classes = self::$em->getMetadataFactory()->getAllMetadata();

            try {
                $schemaTool->createSchema($classes);
            } catch (ToolsException $e) {
                $schemaTool->dropSchema($classes);
                $schemaTool->createSchema($classes);
            }
        }
    }

    private static function cleanEm(): void
    {
        self::$em->clear();

        if (self::createSchema()) {
            (new SchemaTool(self::$em))->dropSchema(self::$em->getMetadataFactory()->getAllMetadata());
        }
    }

    private static function cleanEmCache(): void
    {
        if (is_dir($dir = self::getEmCacheDir())) {
            /** @var \SplFileInfo $file */
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath() ?: $file->getPath());
                } else {
                    unlink($file->getRealPath() ?: $file->getPath());
                }
            }

            rmdir($dir);
        }
    }

    private static function createEntityDistMapping(string $source): string
    {
        $files = [];
        $target = self::getEmCacheDir().'/mapping/'.md5($source);
        mkdir($target, 0777, true);

        /** @var \SplFileInfo $file */
        foreach (new \DirectoryIterator($source) as $file) {
            if ('xml' === $file->getExtension()) {
                $files[] = $file->getPath();
            }
        }

        $config = new MappingConfig($files, ['key_max_length' => 255]);

        foreach ($config->mappingFiles as $file) {
            file_put_contents($target.'/'.basename($file), $config->interpolate((string) file_get_contents($file)));
        }

        return $target;
    }

    abstract protected static function createSchema(): bool;

    abstract protected static function getEntityMappings(): iterable;

    abstract protected static function getEntityIdTypes(): iterable;
}
