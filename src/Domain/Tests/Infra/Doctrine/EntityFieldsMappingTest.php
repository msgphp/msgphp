<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use MsgPhp\Domain\Entity\Features;
use MsgPhp\Domain\Infra\Doctrine\EntityFieldsMapping;
use PHPUnit\Framework\TestCase;

final class EntityFieldsMappingTest extends TestCase
{
    public function testMapping(): void
    {
        $available = array_flip(array_map(function (string $file): string {
            return 'MsgPhp\\Domain\\Entity\\'.basename(dirname($file)).'\\'.basename($file, '.php');
        }, glob(dirname(dirname(dirname(__DIR__))).'/Entity/{Features,Fields}/*.php', \GLOB_BRACE)));
        unset($available[Features\CanBeEnabled::class]);

        $mapping = array_keys(EntityFieldsMapping::getObjectFieldMapping());
        sort($mapping);

        $this->assertSame(array_keys($available), $mapping);
    }
}
