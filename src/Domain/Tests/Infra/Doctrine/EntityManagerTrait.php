<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use MsgPhp\Domain\Tests\Fixtures\DoctrineDomainIdType;

trait EntityManagerTrait
{
    /** @var EntityManager */
    private static $em;

    public static function setUpBeforeClass(): void
    {
        if (Type::hasType('domain_id')) {
            Type::overrideType('domain_id', DoctrineDomainIdType::class);
        } else {
            Type::addType('domain_id', DoctrineDomainIdType::class);
        }

        $config = new Configuration();
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader(), dirname(dirname(__DIR__)).'/Fixtures/Entities'));
        $config->setProxyDir(\sys_get_temp_dir().'/msgphp_'.md5(microtime()));
        $config->setProxyNamespace(__NAMESPACE__);

        self::$em = EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $config);
    }

    public static function tearDownAfterClass(): void
    {
        if (null !== ($proxyDir = self::$em->getConfiguration()->getProxyDir()) && is_dir($proxyDir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($proxyDir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
                @($file->isDir() ? 'rmdir' : 'unlink')($file->getRealPath());
            }

            @rmdir($proxyDir);
        }

        self::$em->close();
        self::$em = null;
    }
}
