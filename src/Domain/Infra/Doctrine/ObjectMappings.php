<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use MsgPhp\Domain\Model;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ObjectMappings implements ObjectMappingProviderInterface
{
    public static function provideObjectMappings(MappingConfig $config): iterable
    {
        yield Model\CanBeConfirmed::class => [
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
        yield Model\CanBeEnabled::class => [
            'enabled' => [
                'type' => 'boolean',
            ],
        ];
        yield Model\CreatedAtField::class => [
            'createdAt' => [
                'type' => 'datetime',
            ],
        ];
        yield Model\LastUpdatedAtField::class => [
            'lastUpdatedAt' => [
                'type' => 'datetime',
            ],
        ];
    }
}
