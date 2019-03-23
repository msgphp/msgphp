<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine;

use MsgPhp\Domain\Infra\Doctrine\MappingConfig;
use MsgPhp\Domain\Infra\Doctrine\ObjectMappingProviderInterface;
use MsgPhp\User\Credential;
use MsgPhp\User\Model;
use MsgPhp\User\Role;
use MsgPhp\User\User;
use MsgPhp\User\UserEmail;
use MsgPhp\User\UserRole;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class ObjectMappings implements ObjectMappingProviderInterface
{
    private const CREDENTIALS = [
        Model\EmailCredential::class => Credential\Email::class,
        Model\EmailPasswordCredential::class => Credential\EmailPassword::class,
        Model\NicknameCredential::class => Credential\Nickname::class,
        Model\NicknamePasswordCredential::class => Credential\NicknamePassword::class,
        Model\TokenCredential::class => Credential\Token::class,
    ];

    public static function provideObjectMappings(MappingConfig $config): iterable
    {
        foreach (self::CREDENTIALS as $model => $credential) {
            yield $model => [
                'credential' => [
                    'type' => self::TYPE_EMBEDDED,
                    'class' => $credential,
                    'columnPrefix' => null,
                ],
            ];
        }

        yield Credential\EmailAsUsername::class => [
            'email' => [
                'type' => 'string',
                'unique' => true,
                'length' => $config->keyMaxLength,
            ],
        ];

        yield Credential\NicknameAsUsername::class => [
            'nickname' => [
                'type' => 'string',
                'unique' => true,
                'length' => $config->keyMaxLength,
            ],
        ];

        yield Credential\PasswordProtection::class => [
            'password' => [
                'type' => 'string',
            ],
        ];

        yield Model\ResettablePassword::class => [
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
        yield Model\EmailsField::class => [
            'emails' => [
                'type' => self::TYPE_ONE_TO_MANY,
                'targetEntity' => UserEmail::class,
                'mappedBy' => 'user',
                'indexBy' => 'email',
            ],
        ];
        yield Model\RoleField::class => [
            'role' => [
                'type' => self::TYPE_MANY_TO_ONE,
                'targetEntity' => Role::class,
                'joinColumns' => [
                    ['referencedColumnName' => 'name', 'nullable' => false],
                ],
            ],
        ];
        yield Model\RolesField::class => [
            'roles' => [
                'type' => self::TYPE_ONE_TO_MANY,
                'targetEntity' => UserRole::class,
                'mappedBy' => 'user',
            ],
        ];
        yield Model\UserField::class => [
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
