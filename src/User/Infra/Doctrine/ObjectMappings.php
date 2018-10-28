<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine;

use MsgPhp\Domain\Infra\Doctrine\{MappingConfig, ObjectMappingProviderInterface};
use MsgPhp\User\Entity\{Credential, Features, Fields, Role, User, UserAttributeValue, UserEmail, UserRole};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ObjectMappings implements ObjectMappingProviderInterface
{
    private const CREDENTIALS = [
        Features\EmailCredential::class => Credential\Email::class,
        Features\EmailPasswordCredential::class => Credential\EmailPassword::class,
        Features\EmailSaltedPasswordCredential::class => Credential\EmailSaltedPassword::class,
        Features\NicknameCredential::class => Credential\Nickname::class,
        Features\NicknamePasswordCredential::class => Credential\NicknamePassword::class,
        Features\NicknameSaltedPasswordCredential::class => Credential\NicknameSaltedPassword::class,
        Features\TokenCredential::class => Credential\Token::class,
    ];

    public static function provideObjectMappings(MappingConfig $config): iterable
    {
        foreach (self::CREDENTIALS as $object => $credential) {
            yield $object => [
                'credential' => [
                    'type' => self::TYPE_EMBEDDED,
                    'class' => $credential,
                    'columnPrefix' => null,
                ],
            ];
        }

        yield Features\ResettablePassword::class => [
            'passwordResetToken' => [
                'type' => 'string',
                'unique' => true,
                'nullable' => true,
                'length' => $config->keyMaxLength,
            ],
            'passwordRequestedAt' => [
                'type' => 'datetime',
                'nullable' => true,
            ],
        ];
        yield Fields\AttributeValuesField::class => [
            'attributeValues' => [
                'type' => self::TYPE_ONE_TO_MANY,
                'targetEntity' => UserAttributeValue::class,
                'mappedBy' => 'user',
            ],
        ];
        yield Fields\EmailsField::class => [
            'emails' => [
                'type' => self::TYPE_ONE_TO_MANY,
                'targetEntity' => UserEmail::class,
                'mappedBy' => 'user',
                'indexBy' => 'email',
            ],
        ];
        yield Fields\RoleField::class => [
            'role' => [
                'type' => self::TYPE_MANY_TO_ONE,
                'targetEntity' => Role::class,
                'joinColumns' => [
                    ['referencedColumnName' => 'name', 'nullable' => false],
                ],
            ],
        ];
        yield Fields\RolesField::class => [
            'roles' => [
                'type' => self::TYPE_ONE_TO_MANY,
                'targetEntity' => UserRole::class,
                'mappedBy' => 'user',
            ],
        ];
        yield Fields\UserField::class => [
            'user' => [
                'type' => self::TYPE_MANY_TO_ONE,
                'targetEntity' => User::class,
                'joinColumns' => [
                    ['nullable' => false],
                ],
            ],
        ];
    }
}
