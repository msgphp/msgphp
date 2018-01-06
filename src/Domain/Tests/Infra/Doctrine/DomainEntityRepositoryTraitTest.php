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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use MsgPhp\Domain\{DomainId, DomainIdInterface};
use MsgPhp\Domain\Exception\{DuplicateEntityException, EntityNotFoundException};
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use PHPUnit\Framework\TestCase;

final class DomainEntityRepositoryTraitTest extends TestCase
{
    /** @var EntityManager|null */
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
        if (null === self::$em) {
            return;
        }

        if (null !== ($proxyDir = self::$em->getConfiguration()->getProxyDir()) && is_dir($proxyDir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($proxyDir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
                @($file->isDir() ? 'rmdir' : 'unlink')($file->getRealPath());
            }

            @rmdir($proxyDir);
        }

        self::$em = null;
    }

    protected function setUp(): void
    {
        if (null === self::$em) {
            throw new \LogicException('No entity manager set.');
        }

        if (!self::$em->isOpen()) {
            self::$em = self::$em::create(self::$em->getConnection(), self::$em->getConfiguration(), self::$em->getEventManager());
        }

        $schema = new SchemaTool(self::$em);
        $schema->dropDatabase();
        $schema->createSchema(self::$em->getMetadataFactory()->getAllMetadata());
    }

    protected function tearDown(): void
    {
        if (null === self::$em) {
            throw new \LogicException('No entity manager set.');
        }

        self::$em->clear();
    }

    /**
     * @dataProvider provideEntities
     */
    public function testFind(string $class, Entities\BaseTestEntity $entity, array $ids): void
    {
        $repository = self::createRepository($class);

        try {
            $repository->doFind(...$ids);

            $this->fail();
        } catch (EntityNotFoundException $e) {
            $this->addToAssertionCount(1);
        }

        $this->loadEntities($entity);

        $this->assertSame($entity, $repository->doFind(...Entities\BaseTestEntity::getPrimaryIds($entity)));
    }

    /**
     * @dataProvider provideEntityFields
     */
    public function testFindByFields(string $class, array $fields): void
    {
        $repository = self::createRepository($class);

        try {
            $repository->doFindByFields($fields);

            $this->fail();
        } catch (\Throwable $e) {
            if ((!$fields && $e instanceof \LogicException) || ($fields && $e instanceof EntityNotFoundException)) {
                $this->addToAssertionCount(1);
            } else {
                throw $e;
            }
        }

        $entity = $class::create($fields);
        $this->loadEntities($entity);

        $this->assertSame($entity, $repository->doFindByFields($fields));
    }

    /**
     * @dataProvider provideEntities
     */
    public function testExists(string $class, Entities\BaseTestEntity $entity, array $ids): void
    {
        $repository = self::createRepository($class);

        $this->assertFalse($repository->doExists(...$ids));

        $this->loadEntities($entity);

        $this->assertTrue($repository->doExists(...Entities\BaseTestEntity::getPrimaryIds($entity)));
    }

    /**
     * @dataProvider provideEntityFields
     */
    public function testExistsByFields(string $class, array $fields): void
    {
        $repository = self::createRepository($class);

        $this->assertFalse($repository->doExistsByFields($fields));

        $this->loadEntities($entity = $class::create($fields));

        $this->assertTrue($repository->doExistsByFields($fields));
    }

    /**
     * @dataProvider provideEntities
     */
    public function testSave(string $class, Entities\BaseTestEntity $entity, array $ids): void
    {
        $repository = self::createRepository($class);

        $this->assertFalse($repository->doExists(...$ids));

        $repository->doSave($entity);

        $this->assertTrue($repository->doExists(...Entities\BaseTestEntity::getPrimaryIds($entity)));
    }

    public function testSaveThrowsOnDuplicate(): void
    {
        $repository = self::createRepository(Entities\TestPrimitiveEntity::class);

        $repository->doSave(Entities\TestPrimitiveEntity::create(['id' => new DomainId('999')]));

        $this->expectException(DuplicateEntityException::class);

        $repository->doSave(Entities\TestPrimitiveEntity::create(['id' => new DomainId('999')]));
    }

    /**
     * @dataProvider provideEntities
     */
    public function testDelete(string $class, Entities\BaseTestEntity $entity): void
    {
        $repository = self::createRepository($class);

        $repository->doSave($entity);

        $this->assertTrue($repository->doExists(...$ids = Entities\BaseTestEntity::getPrimaryIds($entity)));

        $repository->doDelete($entity);

        $this->assertFalse($repository->doExists(...$ids));
    }

    public function provideEntityTypes(): iterable
    {
        yield [Entities\TestEntity::class];
        yield [Entities\TestPrimitiveEntity::class];
        yield [Entities\TestCompositeEntity::class];
        yield [Entities\TestDerivedEntity::class];
        yield [Entities\TestDerivedCompositeEntity::class];
    }

    public function provideEntities(): iterable
    {
        foreach ($this->provideEntityTypes() as $class) {
            $class = $class[0];
            foreach ($class::createEntities() as $entity) {
                $ids = Entities\BaseTestEntity::getPrimaryIds($entity, $primitiveIds);

                yield [$class, $entity,  $ids, $primitiveIds];
            }
        }
    }

    public function provideEntityFields(): iterable
    {
        foreach ($this->provideEntityTypes() as $class) {
            $class = $class[0];

            foreach ($class::getFields() as $fields) {
                yield [$class, $fields];
            }
        }
    }

    private static function createRepository(string $class)
    {
        $em = self::$em;
        $idFields = is_subclass_of($class, Entities\BaseTestEntity::class) ? $class::getIdFields() : ['id'];

        return new class($em, $class, $idFields) {
            use DomainEntityRepositoryTrait {
                doFindAll as public; // @todo
                doFindAllByFields as public; // @todo
                doFind as public;
                doFindByFields as public;
                doExists as public;
                doExistsByFields as public;
                doSave as public;
                doDelete as public;
                __construct as private __parentConstruct;
            }

            private $alias = 'root';
            private $idFields;

            public function __construct(EntityManagerInterface $em, string $class, array $idFields)
            {
                $this->idFields = $idFields;

                $this->__parentConstruct($em, $class);
            }
        };
    }

    private static function persist(Entities\BaseTestEntity $entity): void
    {
        if (null === self::$em) {
            throw new \LogicException('No entity manager set.');
        }

        self::$em->persist($entity);
    }

    private static function flushEntities(iterable $entities): void
    {
        if (null === self::$em) {
            throw new \LogicException('No entity manager set.');
        }

        foreach ($entities as $entity) {
            self::persist($entity);
        }

        self::$em->flush();
    }

    private function loadEntities(Entities\BaseTestEntity ...$context): void
    {
        $entities = [];
        foreach (func_get_args() as $entity) {
            Entities\BaseTestEntity::getPrimaryIds($entity, $primitiveIds);
            $entities[serialize($primitiveIds)] = $entity;
        }

        foreach ($this->provideEntities() as $entity) {
            if (!isset($entities[$primitiveIds = serialize($entity[3])])) {
                $entities[$primitiveIds] = $entity[1];
            }
        }

        self::flushEntities($entities);
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
            return $value->isKnown() ? (int) $value->toString() : null;
        }

        throw ConversionException::conversionFailed($value, $this->getName());
    }

    final public function convertToPHPValue($value, AbstractPlatform $platform): ?DomainIdInterface
    {
        if (null === $value) {
            return null;
        }

        if (is_scalar($value)) {
            return new DomainId((string) $value);
        }

        throw ConversionException::conversionFailed($value, $this->getName());
    }
}
