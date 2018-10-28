<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use MsgPhp\Domain\Entity\{Features, Fields};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ObjectMappings implements ObjectMappingProviderInterface
{
    public static function provideObjectMappings(MappingConfig $config): iterable
    {
        yield Features\CanBeConfirmed::class => [
            'confirmationToken' => [
                'type' => 'string',
                'unique' => true,
                'nullable' => true,
                'length' => $config->keyMaxLength,
            ],
            'confirmedAt' => [
                'type' => 'datetime',
                'nullable' => true,
            ],
        ];
        yield Fields\CreatedAtField::class => [
            'createdAt' => [
                'type' => 'datetime',
            ],
        ];
        yield Fields\EnabledField::class => [
            'enabled' => [
                'type' => 'boolean',
            ],
        ];
        yield Fields\LastUpdatedAtField::class => [
            'lastUpdatedAt' => [
                'type' => 'datetime',
            ],
        ];
    }
}
