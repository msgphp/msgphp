<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use MsgPhp\Domain\Entity\Features;
use MsgPhp\Domain\Infra\Doctrine\ObjectFieldMappings;
use PHPUnit\Framework\TestCase;

final class ObjectFieldMappingsTest extends TestCase
{
    public function testMapping(): void
    {
        $available = array_flip(array_map(function (string $file): string {
            return 'MsgPhp\\Domain\\Entity\\'.basename(\dirname($file)).'\\'.basename($file, '.php');
        }, glob(\dirname(__DIR__, 3).'/Entity/{Features,Fields}/*.php', \GLOB_BRACE)));
        unset($available[Features\CanBeEnabled::class]);

        $mappings = ObjectFieldMappings::provideObjectFieldMappings();
        $mappings = array_keys($mappings instanceof \Traversable ? iterator_to_array($mappings) : $mappings);
        sort($mappings);

        self::assertSame(array_keys($available), $mappings);
    }
}
