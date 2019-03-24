<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Doctrine;

use MsgPhp\Domain\Infrastructure\Doctrine\MappingConfig;
use MsgPhp\Domain\Infrastructure\Doctrine\ObjectMappingProviderInterface;
use MsgPhp\User\Model;
use MsgPhp\User\UserAttributeValue;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class EavObjectMappings implements ObjectMappingProviderInterface
{
    public static function provideObjectMappings(MappingConfig $config): iterable
    {
        yield Model\AttributeValuesField::class => [
            'attributeValues' => [
                'type' => self::TYPE_ONE_TO_MANY,
                'targetEntity' => UserAttributeValue::class,
                'mappedBy' => 'user',
            ],
        ];
    }
}
