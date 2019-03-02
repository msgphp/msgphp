<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine\Hydration;

use MsgPhp\Domain\Infra\Doctrine\Hydration\ScalarHydrator;
use MsgPhp\Domain\Tests\Infra\Doctrine\EntityManagerTestTrait;
use MsgPhp\Domain\Tests\Fixtures\{Entities, TestDomainId};
use PHPUnit\Framework\TestCase;

final class ScalarHydratorTest extends TestCase
{
    use EntityManagerTestTrait;

    public function testHydrator(): void
    {
        self::$em->getConfiguration()->addCustomHydrationMode(ScalarHydrator::NAME, ScalarHydrator::class);
        self::$em->persist($entity = Entities\TestPrimitiveEntity::create(['id' => new TestDomainId('1')]));
        self::$em->flush();

        $query = self::$em->createQuery('SELECT root.id FROM '.\get_class($entity).' root');

        self::assertSame('1', $query->getScalarResult()[0]['id']);
        self::assertSame(1, $query->getResult(ScalarHydrator::NAME)[0]['id']);
    }
}
