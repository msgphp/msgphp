<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use MsgPhp\Domain\Model;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class DomainObjectMappings implements ObjectMappingProvider
{
    public static function provideObjectMappings(MappingConfig $config): iterable
    {
        yield Model\IntIdentity::class => [
            'id' => [
                'id' => true,
                'id_generator' => ClassMetadata::GENERATOR_TYPE_IDENTITY,
                'type' => 'integer',
            ],
        ];
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
