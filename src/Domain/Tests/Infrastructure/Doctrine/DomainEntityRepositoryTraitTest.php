<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infrastructure\Doctrine;

use Doctrine\DBAL\Types\Type;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\Domain\Tests\DomainEntityRepositoryTestCase;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use MsgPhp\Domain\Tests\Fixtures\TestDomainEntityRepository;
use MsgPhp\Domain\Tests\Fixtures\TestDomainId;
use MsgPhp\Domain\Tests\Fixtures\TestDomainIdType;

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

        self::assertSame(Type::INTEGER, $parameters->get(0)->getType());
        self::assertSame(\PDO::PARAM_STR, $parameters->get(1)->getType());
        self::assertSame(TestDomainIdType::NAME, $parameters->get(2)->getType());
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

    /**
     * @inheritdoc
     */
    protected static function createRepository(string $class): TestDomainEntityRepository
    {
        /** @psalm-suppress InaccessibleMethod */
        return new class($class, self::$em) implements TestDomainEntityRepository {
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
