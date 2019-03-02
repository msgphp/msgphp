<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\DBAL\Types\Type;
use MsgPhp\Domain\Infra\Doctrine\{DomainEntityRepositoryTrait, DomainIdType};
use MsgPhp\Domain\Tests\DomainEntityRepositoryTestCase;
use MsgPhp\Domain\Tests\Fixtures\{DomainEntityRepositoryTraitInterface, Entities, TestDomainId};

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
        self::assertSame(DomainIdType::NAME, $parameters->get(2)->getType());
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

    protected function equalsEntity($expected, $actual): bool
    {
        $equals = true;
        foreach (($r = (new \ReflectionObject($expected)))->getProperties() as $property) {
            $property->setAccessible(true);
            $expectedValue = $property->getValue($expected);
            $actualValue = $property->getValue($actual);

            if (\is_object($expectedValue) && \is_object($actualValue)) {
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

    /**
     * @inheritdoc
     */
    protected static function createRepository(string $class): DomainEntityRepositoryTraitInterface
    {
        /** @psalm-suppress InaccessibleMethod */
        return new class($class, self::$em) implements DomainEntityRepositoryTraitInterface {
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
