<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infrastructure\Doctrine;

use MsgPhp\Domain\Infrastructure\Doctrine\Test\EntityManagerTestTrait as BaseEntityManagerTestTrait;

trait EntityManagerTestTrait
{
    use BaseEntityManagerTestTrait;

    protected static function getClassMapping(): array
    {
        return [];
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
        return [];
    }
}
