<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use MsgPhp\Domain\Entity\Fields;
use MsgPhp\Domain\Infra\Doctrine\Mapping\ObjectFieldMappingProviderInterface;

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
