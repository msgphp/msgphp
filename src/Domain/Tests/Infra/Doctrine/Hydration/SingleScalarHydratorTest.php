<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine\Hydration;

use MsgPhp\Domain\Infra\Doctrine\Hydration\SingleScalarHydrator;
use MsgPhp\Domain\Tests\Infra\Doctrine\EntityManagerTestTrait;
use MsgPhp\Domain\Tests\Fixtures\{Entities, TestDomainId};
use PHPUnit\Framework\TestCase;

final class SingleScalarHydratorTest extends TestCase
{
    use EntityManagerTestTrait;

    public function testHydrator(): void
    {
        self::$em->getConfiguration()->addCustomHydrationMode(SingleScalarHydrator::NAME, SingleScalarHydrator::class);
        self::$em->persist($entity = Entities\TestPrimitiveEntity::create(['id' => new TestDomainId('1')]));
        self::$em->flush();

        $query = self::$em->createQuery('SELECT root.id FROM '.\get_class($entity).' root');

        self::assertSame('1', $query->getSingleScalarResult());
        self::assertSame(1, $query->getResult(SingleScalarHydrator::NAME));
        self::assertSame(1, $query->getSingleResult(SingleScalarHydrator::NAME));
    }
}
