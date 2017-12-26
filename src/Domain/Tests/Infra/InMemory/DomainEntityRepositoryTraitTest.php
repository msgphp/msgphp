<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\InMemory;

use MsgPhp\Domain\Exception\DuplicateEntityException;
use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Infra\InMemory\DomainEntityRepositoryTrait;
use MsgPhp\Domain\Infra\InMemory\DomainId;
use PHPUnit\Framework\TestCase;

final class DomainEntityRepositoryTraitTest extends TestCase
{
    public function testWithNoData(): void
    {
        $repository = $this->createRepository();

        $this->assertSame([], iterator_to_array($repository->createResultSet()));
        $this->assertSame([], iterator_to_array($repository->createResultSet(1)));
        $this->assertSame([], iterator_to_array($repository->createResultSet(1, 1)));
        $this->assertSame([], iterator_to_array($repository->createResultSet(null, 1)));

        try {
            $repository->doFind($this->createDomainId());
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

        $this->assertFalse($repository->doExists($this->createDomainId()));
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

        $this->assertSame($users, iterator_to_array($repository->createResultSet()));
        $this->assertSame([$foo2, $foo3], iterator_to_array($repository->createResultSet(1)));
        $this->assertSame([$foo2], iterator_to_array($repository->createResultSet(1, 1)));
        $this->assertSame([$foo1, $foo2], iterator_to_array($repository->createResultSet(null, 2)));

        try {
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

        $this->assertFalse($repository->doExists($this->createDomainId()));
        $this->assertFalse($repository->doExistsByFields(['field' => 'other']));
        $this->assertFalse($repository->doExistsByFields(['field' => 'other', 'FIELD' => 'VALUE']));
        $this->assertTrue($repository->doExists($foo2->id));
        $this->assertTrue($repository->doExistsByFields(['field' => 'value']));
        $this->assertTrue($repository->doExistsByFields(['field' => 'VALUE', 'FIELD' => 'value']));
    }

    public function testResultSetFilter(): void
    {
        $repository = $this->createRepository([
            $entityA = $this->createEntity('A'),
            $entityB = $this->createEntity('B'),
            $entityC = $this->createEntity('C'),
            $entityD = $this->createEntity('D'),
        ]);
        $filter = function (\stdClass $entity) {
            return $entity->id->equals($this->createDomainId('B')) || $entity->id->equals($this->createDomainId('D'));
        };

        $this->assertSame([$entityB, $entityD], iterator_to_array($repository->createResultSet(null, null, $filter)));
        $this->assertSame([], iterator_to_array($repository->createResultSet(2, null, $filter)));
        $this->assertSame([$entityD], iterator_to_array($repository->createResultSet(1, 1, $filter)));
        $this->assertSame([$entityB], iterator_to_array($repository->createResultSet(null, 1, $filter)));
    }

    public function testSaveAndDeleteNewEntiy(): void
    {
        $repository = $this->createRepository();
        $repository->doSave($entity = $this->createEntity());

        $this->assertSame([$entity], iterator_to_array($repository->createResultSet()));
        $this->assertSame($entity, $repository->doFind($entity->id));

        $repository->doDelete($entity);

        $this->assertSame([], iterator_to_array($repository->createResultSet()));
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
        $repository = new class($entities, \stdClass::class) {
            use DomainEntityRepositoryTrait {
                doFind as public;
                doExists as public;
            }

            private $idFields = ['id', 'token'];
        };

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
        return new class($entities, \stdClass::class) {
            use DomainEntityRepositoryTrait {
                doFind as public;
                doFindByFields as public;
                doExists as public;
                doExistsByFields as public;
                doSave as public;
                doDelete as public;
                createResultSet as public;
            }

            private $idFields = ['id'];
        };
    }

    private function createEntity(string $id = null, array $fields = []): \stdClass
    {
        $entity = new \stdClass();
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
