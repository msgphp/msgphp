<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infra\Doctrine;

use MsgPhp\Domain\Infra\Doctrine\MappingConfig;
use MsgPhp\User\Infra\Doctrine\ObjectMappings;
use MsgPhp\User\Model;
use PHPUnit\Framework\TestCase;

final class ObjectMappingsTest extends TestCase
{
    public function testMapping(): void
    {
        $available = array_flip(array_map(function (string $file): string {
            return 'MsgPhp\\User\\Model\\'.basename($file, '.php');
        }, glob(\dirname(__DIR__, 3).'/Model/*.php')));
        unset(
            $available[Model\AbstractCredential::class],
            $available[Model\AbstractPasswordCredential::class]
        );

        $mappings = ObjectMappings::provideObjectMappings(new MappingConfig([]));
        $mappings = array_keys($mappings instanceof \Traversable ? iterator_to_array($mappings) : $mappings);
        sort($mappings);

        self::assertSame(array_keys($available), $mappings);
    }
}
