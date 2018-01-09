<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use MsgPhp\Domain\{DomainId, DomainIdInterface};
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\Domain\Tests\AbstractDomainEntityRepositoryTraitTest;
use MsgPhp\Domain\Tests\Fixtures\DomainEntityRepositoryTraitInterface;

final class DomainEntityRepositoryTraitTest extends AbstractDomainEntityRepositoryTraitTest
{
    /** @var EntityManager */
    private static $em;

    public static function setUpBeforeClass(): void
    {
        AnnotationRegistry::registerLoader('class_exists');
        Type::addType('domain_id', DomainIdType::class);

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

    protected function setUp(): void
    {
        if (!self::$em->isOpen()) {
            self::$em = self::$em::create(self::$em->getConnection(), self::$em->getConfiguration(), self::$em->getEventManager());
        }

        (new SchemaTool(self::$em))->createSchema(self::$em->getMetadataFactory()->getAllMetadata());
    }

    protected function tearDown(): void
    {
        (new SchemaTool(self::$em))->dropDatabase();

        self::$em->clear();
    }

    protected function equalsEntity($expected, $actual)
    {
        $equals = true;
        foreach (($r = (new \ReflectionObject($expected)))->getProperties() as $property) {
            $property->setAccessible(true);
            $expectedValue = $property->getValue($expected);
            $actualValue = $property->getValue($actual);

            if (is_object($expectedValue) && is_object($actualValue)) {
                if (!$this->equalsEntity($expectedValue, $actualValue)) {
                    $equals = false;
                    break;
                }

                continue;
            }

            if ($expectedValue !== $actualValue) {
                $equals = false;
                break;
            }
        }

        return $equals;
    }

    protected static function createRepository(string $class): DomainEntityRepositoryTraitInterface
    {
        $em = self::$em;

        return new class($class, $em) implements DomainEntityRepositoryTraitInterface {
            use DomainEntityRepositoryTrait {
                doFindAll as public;
                doFindAllByFields as public;
                doFind as public;
                doFindByFields as public;
                doExists as public;
                doExistsByFields as public;
                doSave as public;
                doDelete as public;
            }

            private $alias = 'root';
        };
    }

    protected static function flushEntities(iterable $entities): void
    {
        foreach ($entities as $entity) {
            self::$em->persist($entity);
        }

        self::$em->flush();
    }
}

/**
 * @fixme should be core doctrine infra
 */
class DomainIdType extends IntegerType
{
    public function getName()
    {
        return 'domain_id';
    }

    final public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof DomainIdInterface) {
            return $value->isEmpty() ? null : (int) $value->toString();
        }

        throw ConversionException::conversionFailed($value, $this->getName());
    }

    final public function convertToPHPValue($value, AbstractPlatform $platform): DomainIdInterface
    {
        if (null === $value) {
            return new DomainId();
        }

        if (is_scalar($value)) {
            return new DomainId((string) $value);
        }

        throw ConversionException::conversionFailed($value, $this->getName());
    }
}
