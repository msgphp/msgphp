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

    public function testFindAll(): void
    {
        $repository = self::createRepository(Entities\TestPrimitiveEntity::class);
        $entities = [
            $entity1 = Entities\TestPrimitiveEntity::create(['id' => new DomainId('1')]),
            $entity2 = Entities\TestPrimitiveEntity::create(['id' => new DomainId('2')]),
            $entity3 = Entities\TestPrimitiveEntity::create(['id' => new DomainId('3')]),
        ];

        $this->assertSame([], iterator_to_array($repository->doFindAll()));
        $this->assertSame([], iterator_to_array($repository->doFindAll(1)));
        $this->assertSame([], iterator_to_array($repository->doFindAll(1, 1)));
        $this->assertSame([], iterator_to_array($repository->doFindAll(1, 0)));
        $this->assertSame([], iterator_to_array($repository->doFindAll(0, 10)));
        $this->assertSame([], iterator_to_array($repository->doFindAll(10, 10)));

        self::flushEntities($entities);

        $this->assertSame($entities, iterator_to_array($repository->doFindAll()));
        $this->assertSame([$entity2, $entity3], iterator_to_array($repository->doFindAll(1)));
        $this->assertSame([$entity2], iterator_to_array($repository->doFindAll(1, 1)));
        $this->assertSame([$entity2,  $entity3], iterator_to_array($repository->doFindAll(1, 0)));
        $this->assertSame($entities, iterator_to_array($repository->doFindAll(0, 10)));
        $this->assertSame([], iterator_to_array($repository->doFindAll(10, 10)));
        $this->assertSame([$entity1, $entity2], iterator_to_array($repository->doFindAll(0, 2)));
    }

    public function testFindAllByFields(): void
    {
        $repository = self::createRepository(Entities\TestPrimitiveEntity::class);
        $entities = [
            $entity1 = Entities\TestPrimitiveEntity::create(['id' => new DomainId('1')]),
            $entity2 = Entities\TestPrimitiveEntity::create(['id' => new DomainId('2')]),
            $entity3 = Entities\TestPrimitiveEntity::create(['id' => new DomainId('3')]),
        ];

        $this->assertSame([], iterator_to_array($repository->doFindAllByFields(['id' => 1])));
        $this->assertSame([], iterator_to_array($repository->doFindAllByFields(['id' => new DomainId()], 1)));
        $this->assertSame([], iterator_to_array($repository->doFindAllByFields(['id' => [new DomainId('2'), new DomainId('3')]], 1, 1)));
        $this->assertSame([], iterator_to_array($repository->doFindAllByFields(['id' => [new DomainId('2'), new DomainId('1'), new DomainId()]], 1, 0)));
        $this->assertSame([], iterator_to_array($repository->doFindAllByFields(['id' => [new DomainId('1'), new DomainId('3')]], 0, 10)));
        $this->assertSame([], iterator_to_array($repository->doFindAllByFields(['id' => new DomainId('3')], 0, 10)));
        $this->assertSame([], iterator_to_array($repository->doFindAllByFields(['id' => new DomainId('2')], 10, 10)));
        $this->assertSame([], iterator_to_array($repository->doFindAllByFields(['id' => new DomainId('1')], 0, 2)));

        self::flushEntities($entities);

        $this->assertSame([$entity1], iterator_to_array($repository->doFindAllByFields(['id' => 1])));
        $this->assertSame([], iterator_to_array($repository->doFindAllByFields(['id' => new DomainId()], 1)));
        $this->assertSame([$entity3], iterator_to_array($repository->doFindAllByFields(['id' => [new DomainId('2'), new DomainId('3')]], 1, 1)));
        $this->assertSame([$entity2], iterator_to_array($repository->doFindAllByFields(['id' => [new DomainId('2'), new DomainId('1'), new DomainId()]], 1, 0)));
        $this->assertSame([$entity1, $entity3], iterator_to_array($repository->doFindAllByFields(['id' => [new DomainId('1'), new DomainId('3')]], 0, 10)));
        $this->assertSame([$entity3], iterator_to_array($repository->doFindAllByFields(['id' => new DomainId('3')], 0, 10)));
        $this->assertSame([], iterator_to_array($repository->doFindAllByFields(['id' => new DomainId('2')], 10, 10)));
        $this->assertSame([$entity1], iterator_to_array($repository->doFindAllByFields(['id' => new DomainId('1')], 0, 2)));

        $this->expectException(\LogicException::class);

        $repository->doFindAllByFields([]);
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

        $this->assertEquals($entity, $repository->doFind(...Entities\BaseTestEntity::getPrimaryIds($entity)));
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
        } catch (EntityNotFoundException $e) {
            $this->addToAssertionCount(1);
        }

        $entity = $class::create($fields);
        $this->loadEntities($entity);

        $this->assertEquals($entity, $repository->doFindByFields($fields));
    }

    public function testFindByFieldsWithNoFields(): void
    {
        $repository = self::createRepository(Entities\TestEntity::class);

        $this->expectException(\LogicException::class);

        $repository->doExistsByFields([]);
    }

    public function testFindByFieldsWithPrimaryId(): void
    {
        $repository = self::createRepository(Entities\TestDerivedEntity::class);
        $entity = Entities\TestEntity::create([
            'intField' => -1,
            'boolField' => true,
        ]);
        $entity2 = Entities\TestEntity::create([
            'intField' => -1,
            'boolField' => true,
        ]);

        // https://github.com/doctrine/doctrine2/issues/4584
        $entity->identify(new DomainId('IRRELEVANT'));

        $this->assertTrue($entity2->getId()->isEmpty());

        self::flushEntities([$derivingEntity = Entities\TestDerivedEntity::create(['entity' => $entity]), $entity2]);

        $this->assertNotSame('IRRELEVANT', $entity->getId()->toString());

        $this->assertEquals($derivingEntity, $repository->doFindByFields(['entity' => $entity->getId()]));
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

    public function testExistsByFieldsWithNoFields(): void
    {
        $repository = self::createRepository(Entities\TestEntity::class);

        $this->expectException(\LogicException::class);

        $repository->doExistsByFields([]);
    }

    public function testExistsByFieldsWithEmptyDomainId(): void
    {
        $repository = self::createRepository(Entities\TestDerivedEntity::class);
        $entity = Entities\TestEntity::create([
            'intField' => -1,
            'boolField' => true,
        ]);

        $this->assertFalse($repository->doExistsByFields(['entity' => $entity]));
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

    public function testSaveUpdates(): void
    {
        $repository = self::createRepository(Entities\TestEntity::class);
        $entity = Entities\TestEntity::create([
            'intField' => 1,
            'floatField' => -1.23,
            'boolField' => false,
        ]);

        $repository->doSave($entity);

        $this->assertInstanceOf(DomainIdInterface::class, $entity->getId());
        $this->assertFalse($entity->getId()->isEmpty());
        $this->assertNull($entity->strField);
        $this->assertSame(1, $entity->intField);
        $this->assertSame(-1.23, $entity->floatField);
        $this->assertFalse($entity->boolField);

        $entity->strField = 'foo';
        $entity->floatField = null;
        $entity->boolField = true;

        $repository->doSave($entity);

        self::$em->clear();

        $this->assertNotSame($entity, $entity = $repository->doFind($entity->getId()->toString()));
        $this->assertInstanceOf(Entities\TestEntity::class, $entity);
        $this->assertSame('foo', $entity->strField);
        $this->assertSame(1, $entity->intField);
        $this->assertNull($entity->floatField);
        $this->assertTrue($entity->boolField);
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

        self::flushEntities([$entity]);

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
                doFindAll as public;
                doFindAllByFields as public;
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

    private static function flushEntities(iterable $entities): void
    {
        foreach ($entities as $entity) {
            self::$em->persist($entity);
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
