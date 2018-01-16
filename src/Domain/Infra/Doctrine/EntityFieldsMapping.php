<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use MsgPhp\Domain\Entity\{Features, Fields};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class EntityFieldsMapping implements ObjectFieldMappingProviderInterface
{
    public static function getObjectFieldMapping(): array
    {
        return [
            Features\CanBeConfirmed::class => [
                'confirmationToken' => [
                    'type' => 'string',
                    'unique' => true,
                    'nullable' => true,
                ],
                'confirmedAt' => [
                    'type' => 'datetime',
                    'nullable' => true,
                ],
            ],
            Fields\CreatedAtField::class => [
                'createdAt' => [
                    'type' => 'datetime',
                ],
            ],
            Fields\EnabledField::class => [
                'enabled' => [
                    'type' => 'boolean',
                ],
            ],
            Fields\LastUpdatedAtField::class => [
                'lastUpdatedAt' => [
                    'type' => 'datetime',
                ],
            ],
        ];
    }
}
