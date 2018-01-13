<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine;

use MsgPhp\Domain\Infra\Doctrine\Mapping\ObjectFieldMappingProviderInterface;
use MsgPhp\User\Entity\{Fields, User};

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
            Fields\UserField::class => [
                'user' => [
                    'type' => self::TYPE_MANY_TO_ONE,
                    'targetEntity' => User::class,
                    'joinColumns' => [
                        ['nullable' => false],
                    ],
                ],
            ],
        ];
    }
}
