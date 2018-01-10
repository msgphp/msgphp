<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\InMemory;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Exception\{DuplicateEntityException, EntityNotFoundException};
use MsgPhp\Domain\Infra\InMemory\{DomainEntityRepositoryTrait, DomainIdentityMap, GlobalObjectMemory};
use PHPUnit\Framework\TestCase;

final class DomainEntityRepositoryTraitTest extends TestCase
{
    public function testWithNoData(): void
    {
        $repository = $this->createRepository();

        $this->assertSame([], iterator_to_array($repository->doFindAll()));
        $this->assertSame([], iterator_to_array($repository->doFindAll(1)));
        $this->assertSame([], iterator_to_array($repository->doFindAll(1, 1)));
        $this->assertSame([], iterator_to_array($repository->doFindAll(0, 1)));

        try {
            $repository->doFind(new DomainId());
            $this->fail();
        } catch (EntityNotFoundException $e) {
            $this->assertTrue(true);
        }

        try {
            $repository->doFindByFields(['field' => 'value', 'other' => true]);
            $this->fail();
        } catch (EntityNotFoundException $e) {
            $this->assertTrue(true);
        }

        $this->assertFalse($repository->doExists(new DomainId()));
        $this->assertFalse($repository->doExistsByFields(['field' => null]));

        $repository->doDelete($this->createEntity());
    }

    public function testWithData(): void
    {
        $repository = $this->createRepository($users = [
            $foo1 = $this->createEntity(null, ['field' => null, 'FIELD' => true]),
            $foo2 = $this->createEntity(null, ['field' => 'value', 'FIELD' => 'VALUE']),
            $foo3 = $this->createEntity(null, ['field' => 'VALUE', 'FIELD' => 'value']),
        ]);

        $this->assertSame($users, iterator_to_array($repository->doFindAll()));
        $this->assertSame([$foo2, $foo3], iterator_to_array($repository->doFindAll(1)));
        $this->assertSame([$foo2], iterator_to_array($repository->doFindAll(1, 1)));
        $this->assertSame([$foo1, $foo2], iterator_to_array($repository->doFindAll(0, 2)));

        try {
            $foo2->id = 1;
            $this->assertSame($foo2, $repository->doFind($foo2->id));
        } catch (EntityNotFoundException $e) {
            $this->fail($e->getMessage());
        }

        try {
            $this->assertSame($foo1, $repository->doFindByFields(['field' => null]));
        } catch (EntityNotFoundException $e) {
            $this->fail($e->getMessage());
        }

        try {
            $this->assertSame($foo2, $repository->doFindByFields(['FIELD' => 'VALUE']));
        } catch (EntityNotFoundException $e) {
            $this->fail($e->getMessage());
        }

        try {
            $this->assertSame($foo3, $repository->doFindByFields(['field' => 'VALUE']));
        } catch (EntityNotFoundException $e) {
            $this->fail($e->getMessage());
        }

        try {
            $repository->doFindByFields(['field' => 'VALUE', 'FIELD' => 'VALUE']);
            $this->fail();
        } catch (EntityNotFoundException $e) {
            $this->assertTrue(true);
        }

        try {
            $repository->doFindByFields(['field' => 'value', 'FIELD' => true]);
            $this->fail();
        } catch (EntityNotFoundException $e) {
            $this->assertTrue(true);
        }

        $this->assertFalse($repository->doExists(new DomainId()));
        $this->assertFalse($repository->doExistsByFields(['field' => 'other']));
        $this->assertFalse($repository->doExistsByFields(['field' => 'other', 'FIELD' => 'VALUE']));
        $this->assertTrue($repository->doExists($foo2->id));
        $this->assertTrue($repository->doExistsByFields(['field' => 'value']));
        $this->assertTrue($repository->doExistsByFields(['field' => 'VALUE', 'FIELD' => 'value']));
    }

    public function testSaveAndDeleteNewEntiy(): void
    {
        $repository = $this->createRepository();
        $repository->doSave($entity = $this->createEntity());
        $entity->id = 1;

        $this->assertSame([$entity], iterator_to_array($repository->doFindAll()));
        $this->assertSame($entity, $repository->doFind($entity->id));

        $repository->doDelete($entity);

        $this->assertSame([], iterator_to_array($repository->doFindAll()));
    }

    public function testSaveDuplicateEntity(): void
    {
        $repository = $this->createRepository();
        $repository->doSave($this->createEntity('1'));

        $this->expectException(DuplicateEntityException::class);

        $repository->doSave($this->createEntity('1'));
    }

    public function testCompositePrimaryFields(): void
    {
        $entities = [
            $entityA1 = $this->createEntity('A', ['token' => '1']),
            $entityA2 = $this->createEntity('A', ['token' => '2']),
            $entityB1 = $this->createEntity('B', ['token' => '1']),
        ];
        $repository = new class(\stdClass::class) {
            use DomainEntityRepositoryTrait {
                doFind as public;
                doExists as public;
                doSave as public;
                __construct as private __parentConstruct;
            }

            public function __construct($class)
            {
                $this->__parentConstruct($class, new DomainIdentityMap([$class => ['id', 'token']]), new GlobalObjectMemory());
            }
        };

        foreach ($entities as $entity) {
            $repository->doSave($entity);
        }

        $this->assertTrue($repository->doExists($entityA1->id, '1'));
        $this->assertFalse($repository->doExists($entityA1->id, 'other'));
        $this->assertFalse($repository->doExists('other', '1'));
        $this->assertSame($entityA2, $repository->doFind($entityA2->id, '2'));

        try {
            $repository->doFind($entityB1->id, 'other');
            $this->fail();
        } catch (EntityNotFoundException $e) {
            $this->assertTrue(true);
        }
    }

    private function createRepository(array $entities = [])
    {
        $repository = new class(\stdClass::class) {
            use DomainEntityRepositoryTrait {
                doFindAll as public;
                doFind as public;
                doFindByFields as public;
                doExists as public;
                doExistsByFields as public;
                doSave as public;
                doDelete as public;
                __construct as private __parentConstruct;
            }

            public function __construct($class)
            {
                $this->__parentConstruct($class, new DomainIdentityMap([$class => 'id']), new GlobalObjectMemory());
            }
        };

        foreach ($entities as $entity) {
            $repository->doSave($entity);
        }

        return $repository;
    }

    private function createEntity(string $id = null, array $fields = []): \stdClass
    {
        $entity = new \stdClass();
        $entity->id = new DomainId($id);

        foreach ($fields as $field => $value) {
            $entity->$field = $value;
        }

        return $entity;
    }
}
