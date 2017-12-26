<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use MsgPhp\Domain\Exception\DuplicateEntityException;
use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\Domain\Infra\InMemory\DomainId;
use PHPUnit\Framework\TestCase;

final class DomainEntityRepositoryTraitTest extends TestCase
{
    public function testWithNoData(): void
    {
        $repository = $this->createRepository();

        $this->assertSame([], iterator_to_array($repository->createResultSet($repository->createQueryBuilder()->getQuery())));
        $this->assertSame([], iterator_to_array($repository->createResultSet($repository->createQueryBuilder(1)->getQuery())));
        $this->assertSame([], iterator_to_array($repository->createResultSet($repository->createQueryBuilder(1, 1)->getQuery())));
        $this->assertSame([], iterator_to_array($repository->createResultSet($repository->createQueryBuilder(null, 1)->getQuery())));

        try {
            $repository->doFind($this->createDomainId());
            $this->fail();
        } catch (EntityNotFoundException $e) {
            $this->assertTrue(true);
        }

        try {
            $repository->doFindByFields(['field' => 'value', 'field2' => true]);
            $this->fail();
        } catch (EntityNotFoundException $e) {
            $this->assertTrue(true);
        }

        $this->assertFalse($repository->doExists($this->createDomainId()));
        $this->assertFalse($repository->doExistsByFields(['field' => null]));

        $repository->doDelete($this->createEntity());
    }

    public function testWithData(): void
    {
        $repository = $this->createRepository($users = [
            $foo1 = $this->createEntity(null, ['field' => null, 'field2' => true]),
            $foo2 = $this->createEntity(null, ['field' => 'value', 'field2' => 'VALUE']),
            $foo3 = $this->createEntity(null, ['field' => 'VALUE', 'field2' => 'value']),
        ]);

        $this->assertEquals($users, iterator_to_array($repository->createResultSet($repository->createQueryBuilder()->getQuery())));
        $this->assertEquals([$foo2, $foo3], iterator_to_array($repository->createResultSet($repository->createQueryBuilder(1)->getQuery())));
        $this->assertEquals([$foo2], iterator_to_array($repository->createResultSet($repository->createQueryBuilder(1, 1)->getQuery())));
        $this->assertEquals([$foo1, $foo2], iterator_to_array($repository->createResultSet($repository->createQueryBuilder(null, 2)->getQuery())));

        try {
            $this->assertEquals($foo2, $repository->doFind($foo2->id));
        } catch (EntityNotFoundException $e) {
            $this->fail($e->getMessage());
        }

        try {
            $this->assertEquals($foo1, $repository->doFindByFields(['field' => null]));
        } catch (EntityNotFoundException $e) {
            $this->fail($e->getMessage());
        }

        try {
            $this->assertEquals($foo2, $repository->doFindByFields(['field2' => 'VALUE']));
        } catch (EntityNotFoundException $e) {
            $this->fail($e->getMessage());
        }

        try {
            $this->assertEquals($foo3, $repository->doFindByFields(['field' => 'VALUE']));
        } catch (EntityNotFoundException $e) {
            $this->fail($e->getMessage());
        }

        try {
            $repository->doFindByFields(['field' => 'VALUE', 'field2' => 'VALUE']);
            $this->fail();
        } catch (EntityNotFoundException $e) {
            $this->assertTrue(true);
        }

        try {
            $repository->doFindByFields(['field' => 'value', 'field2' => true]);
            $this->fail();
        } catch (EntityNotFoundException $e) {
            $this->assertTrue(true);
        }

        $this->assertFalse($repository->doExists($this->createDomainId()));
        $this->assertFalse($repository->doExistsByFields(['field' => 'other']));
        $this->assertFalse($repository->doExistsByFields(['field' => 'other', 'field2' => 'VALUE']));
        $this->assertTrue($repository->doExists($foo2->id));
        $this->assertTrue($repository->doExistsByFields(['field' => 'value']));
        $this->assertTrue($repository->doExistsByFields(['field' => 'VALUE', 'field2' => 'value']));
    }

    public function testSaveAndDeleteNewEntiy(): void
    {
        $repository = $this->createRepository();
        $repository->doSave($entity = $this->createEntity());

        $this->assertEquals([$entity], iterator_to_array($repository->createResultSet($repository->createQueryBuilder()->getQuery())));
        $this->assertEquals($entity, $repository->doFind($entity->id));

        $repository->doDelete($entity);

        $this->assertEquals([], iterator_to_array($repository->createResultSet($repository->createQueryBuilder()->getQuery())));
    }

    public function testSaveDuplicateEntity(): void
    {
        $repository = $this->createRepository();
        $repository->doSave($this->createEntity('1'));

        $this->expectException(DuplicateEntityException::class);

        $repository->doSave($this->createEntity('1'));
    }

    private function createRepository(array $entities = [])
    {
        $em = $this->createEntityManager($entities);

        return new class($em, TestEntity::class) {
            use DomainEntityRepositoryTrait {
                doFind as public;
                doFindByFields as public;
                doExists as public;
                doExistsByFields as public;
                doSave as public;
                doDelete as public;
                createResultSet as public;
                createQueryBuilder as public;
            }

            private $alias = 'test_entity';
            private $idFields = ['id'];
        };
    }

    private function createEntityManager(array $entities): EntityManagerInterface
    {
        AnnotationRegistry::registerLoader('class_exists');
        if (Type::hasType('domain_id')) {
            Type::overrideType('domain_id', DomainIdType::class);
        } else {
            Type::addType('domain_id', DomainIdType::class);
        }

        $config = new Configuration();
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader(), __DIR__));
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setProxyNamespace(__NAMESPACE__);

        $em = EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $config);

        $schema = new SchemaTool($em);
        $schema->dropDatabase();
        $schema->createSchema($em->getMetadataFactory()->getAllMetadata());

        if ($entities) {
            foreach ($entities as $entity) {
                $em->persist($entity);
            }

            $em->flush();
        }

        $em->getUnitOfWork()->clear();

        return $em;
    }

    private function createEntity(string $id = null, array $fields = []): TestEntity
    {
        $entity = new TestEntity();
        $entity->id = $this->createDomainId($id);

        foreach ($fields as $field => $value) {
            $entity->$field = $value;
        }

        return $entity;
    }

    private function createDomainId(string $id = null): DomainId
    {
        return new DomainId($id);
    }
}

class DomainIdType extends StringType
{
    public function getName()
    {
        return 'domain_id';
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (string) $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return new DomainId($value);
    }
}

/**
 * @Doctrine\ORM\Mapping\Entity()
 */
class TestEntity
{
    /**
     * @Doctrine\ORM\Mapping\Id()
     * @Doctrine\ORM\Mapping\Column(type="domain_id")
     */
    public $id;
    /**
     * @Doctrine\ORM\Mapping\Column(nullable=true)
     */
    public $field;
    /**
     * @Doctrine\ORM\Mapping\Column(nullable=true)
     */
    public $field2;
}
