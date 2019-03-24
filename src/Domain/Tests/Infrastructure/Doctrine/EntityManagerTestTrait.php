<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infrastructure\Doctrine;

use MsgPhp\Domain\Infrastructure\Doctrine\Test\EntityManagerTestTrait as BaseEntityManagerTestTrait;
use MsgPhp\Domain\Tests\Fixtures;

trait EntityManagerTestTrait
{
    use BaseEntityManagerTestTrait {
        initEm as public setUpBeforeClass;
        destroyEm as public tearDownAfterClass;
    }

    protected function setUp(): void
    {
        self::prepareEm();
    }

    protected function tearDown(): void
    {
        self::cleanEm();
    }

    protected static function createSchema(): bool
    {
        return true;
    }

    protected static function getEntityMappings(): iterable
    {
        yield 'annot' => [
            'MsgPhp\\Domain\\Tests\\Fixtures\\Entities\\' => \dirname(__DIR__, 2).'/Fixtures/Entities',
        ];
    }

    protected static function getEntityIdTypes(): iterable
    {
        yield Fixtures\TestDomainIdType::class => Fixtures\TestDomainId::class;
        yield Fixtures\TestOtherDomainIdType::class => Fixtures\TestOtherDomainId::class;
    }
}
