<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use MsgPhp\Domain\Infra\Doctrine\DomainIdType;

trait EntityManagerTrait
{
    /** @var EntityManager */
    private static $em;

    public static function setUpBeforeClass(): void
    {
        if (Type::hasType(DomainIdType::NAME)) {
            Type::overrideType(DomainIdType::NAME, DomainIdType::class);
        } else {
            Type::addType(DomainIdType::NAME, DomainIdType::class);
        }

        $config = new Configuration();
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader(), dirname(dirname(__DIR__)).'/Fixtures/Entities'));
        $config->setProxyDir(\sys_get_temp_dir().'/msgphp_'.md5(microtime()));
        $config->setProxyNamespace(__NAMESPACE__);

        self::$em = EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $config);
    }

    public static function tearDownAfterClass(): void
    {
        $proxyDir = self::$em->getConfiguration()->getProxyDir();

        if (null !== $proxyDir && is_dir($proxyDir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($proxyDir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
                @($file->isDir() ? 'rmdir' : 'unlink')($file->getRealPath());
            }

            @rmdir($proxyDir);
        }

        self::$em->close();
        self::$em = null;
    }

    protected function setUp(): void
    {
        if (!$this->createSchema) {
            return;
        }

        if (!self::$em->isOpen()) {
            self::$em = self::$em::create(self::$em->getConnection(), self::$em->getConfiguration(), self::$em->getEventManager());
        }

        (new SchemaTool(self::$em))->createSchema(self::$em->getMetadataFactory()->getAllMetadata());
    }

    protected function tearDown(): void
    {
        if (!$this->createSchema) {
            return;
        }

        (new SchemaTool(self::$em))->dropDatabase();

        self::$em->clear();
    }
}
