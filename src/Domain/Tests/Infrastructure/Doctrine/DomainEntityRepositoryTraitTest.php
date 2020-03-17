<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infrastructure\Doctrine;

use Doctrine\DBAL\Types\Type;
use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\Domain\Tests\DomainEntityRepositoryTestCase;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use MsgPhp\Domain\Tests\Fixtures\T;
use MsgPhp\Domain\Tests\Fixtures\TestDomainEntityRepository;
use MsgPhp\Domain\Tests\Fixtures\TestDomainId;

/**
 * @internal
 */
final class DomainEntityRepositoryTraitTest extends DomainEntityRepositoryTestCase
{
    use EntityManagerTestTrait;

    public function testGetAlias(): void
    {
        $repository = new class(Entities\TestEntity::class, self::$em) {
            use DomainEntityRepositoryTrait {
                getAlias as public;
            }
        };

        self::assertSame('test_entity', $repository->getAlias());
    }

    public function testAddFieldParameter(): void
    {
        $repository = new class(Entities\TestEntity::class, self::$em) {
            use DomainEntityRepositoryTrait {
                createQueryBuilder as public;
                addFieldParameter as public;
            }
        };
        $qb = $repository->createQueryBuilder();

        self::assertSame(':id', $repository->addFieldParameter($qb, 'id', 1));
        self::assertSame(':id1', $repository->addFieldParameter($qb, 'id', '1'));
        self::assertSame(':id2', $repository->addFieldParameter($qb, 'id', new TestDomainId('1')));

        $parameters = $qb->getParameters();

        /** @psalm-suppress PossiblyNullReference */
        self::assertSame(Type::INTEGER, $parameters->get(0)->getType());
        /** @psalm-suppress PossiblyNullReference */
        self::assertSame(\PDO::PARAM_STR, $parameters->get(1)->getType());
        /** @psalm-suppress PossiblyNullReference */
        self::assertSame(Type::INTEGER, $parameters->get(2)->getType());
    }

    public function testToIdentity(): void
    {
        $repository = new class(Entities\TestEntity::class, self::$em) {
            use DomainEntityRepositoryTrait {
                toIdentity as public;
            }
        };

        self::assertSame(['id' => 1], $repository->toIdentity(1));
        self::assertSame(['id' => '1'], $repository->toIdentity('1'));
        self::assertSame(['id' => 1], $repository->toIdentity(new TestDomainId('1')));
        self::assertSame(['id' => 1], $repository->toIdentity(['id' => 1]));
        self::assertSame(['id' => '1'], $repository->toIdentity(['id' => '1']));
        self::assertSame(['id' => 1], $repository->toIdentity(['id' => new TestDomainId('1')]));
        self::assertNull($repository->toIdentity(['id' => 1, 'foo' => 'bar']));
        self::assertNull($repository->toIdentity(null));
        self::assertNull($repository->toIdentity([]));
        self::assertNull($repository->toIdentity(['foo' => 'bar']));
    }

    protected static function createRepository(string $class): TestDomainEntityRepository
    {
        return new TestEntityRepository($class, self::$em);
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
 * @template T of object
 * @implements TestDomainEntityRepository<T>
 */
final class TestEntityRepository implements TestDomainEntityRepository
{
    /** @use DomainEntityRepositoryTrait<T> */
    use DomainEntityRepositoryTrait;

    public function findAll(int $offset = 0, int $limit = 0): DomainCollection
    {
        return $this->doFindAll(...\func_get_args());
    }

    public function findAllByFields(array $fields, int $offset = 0, int $limit = 0): DomainCollection
    {
        return $this->doFindAllByFields(...\func_get_args());
    }

    public function find($id): object
    {
        return $this->doFind(...\func_get_args());
    }

    public function findByFields(array $fields): object
    {
        return $this->doFindByFields(...\func_get_args());
    }

    public function exists($id): bool
    {
        return $this->doExists(...\func_get_args());
    }

    public function existsByFields(array $fields): bool
    {
        return $this->doExistsByFields(...\func_get_args());
    }

    public function save(object $entity): void
    {
        $this->doSave(...\func_get_args());
    }

    public function delete(object $entity): void
    {
        $this->doDelete(...\func_get_args());
    }
}
