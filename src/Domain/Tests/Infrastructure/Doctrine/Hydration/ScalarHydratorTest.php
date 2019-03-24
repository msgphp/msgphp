<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infrastructure\Doctrine\Hydration;

use MsgPhp\Domain\Infrastructure\Doctrine\Hydration\ScalarHydrator;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use MsgPhp\Domain\Tests\Fixtures\TestDomainId;
use MsgPhp\Domain\Tests\Infrastructure\Doctrine\EntityManagerTestTrait;
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
