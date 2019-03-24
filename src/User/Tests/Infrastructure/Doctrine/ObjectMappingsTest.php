<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Infrastructure\Doctrine;

use MsgPhp\Domain\Infrastructure\Doctrine\MappingConfig;
use MsgPhp\User\Credential;
use MsgPhp\User\Infrastructure\Doctrine\ObjectMappings;
use MsgPhp\User\Model;
use PHPUnit\Framework\TestCase;

final class ObjectMappingsTest extends TestCase
{
    public function testMapping(): void
    {
        $available = array_flip([
            Credential\EmailAsUsername::class,
            Credential\NicknameAsUsername::class,
            Credential\PasswordProtection::class,
        ]);
        $available += array_flip(array_map(function (string $file): string {
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
